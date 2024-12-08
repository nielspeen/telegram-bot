<?php

namespace App\Services;

use OpenAI;
use App\Models\User;
use App\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Moderator
{
    protected function createOpenAIChat(string $prompt, ?string $imageData = null): string
    {
        $openai = OpenAI::client(config('openai.api_key'));

        $messages = [
            'role' => 'user',
            'content' => $imageData
                ? [
                    [
                        'type' => 'text',
                        'text' => $prompt,
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:image/jpeg;base64,' . $imageData,
                        ],
                    ],
                ]
                : $prompt,
        ];

        $result = $openai->chat()->create([
            'model' => $imageData ? 'gpt-4o' : 'gpt-4o-mini',
            'messages' => [$messages],
        ]);

        return $result->choices[0]->message->content;
    }

    public function moderatePhoto(object $message): void
    {
        if ($this->banBots($message)) {
            return;
        }

        if ($this->rateLimit($message)) {
            return;
        }

        Log::info('Moderating photo:', ['chat_id' => $message->chat->title, 'message_thread_id' => $message->message_thread_id ?? null, 'username' => $message->from->username, 'photo' => $message->photo]);

        $filename = Telegram::getFile($message->photo[0]->file_id, $message->photo[0]->file_unique_id);
        Log::info('Photo:', ['filename' => $filename]);

        $prompt = $this->getPromptPhoto($message);
        $imageData = base64_encode(file_get_contents($filename));

        $responseContent = $this->createOpenAIChat($prompt, $imageData);
        Log::info('Response:', ['content' => $responseContent]);

        $this->handleModerationResponse($message, $responseContent);
    }

    public function moderate(object $message): void
    {
        if ($this->banBots($message)) {
            return;
        }

        if ($this->rateLimit($message)) {
            return;
        }

        Log::info('Moderating message:', ['chat_id' => $message->chat->title, 'message_thread_id' => $message->message_thread_id ?? null, 'username' => $message->from->username, 'text' => $message->text]);

        $prompt = $this->getPrompt($message);
        $responseContent = $this->createOpenAIChat($prompt);

        $this->handleModerationResponse($message, $responseContent);
    }

    protected function getPrompt(object $message, string $type = 'message'): string
    {
        $text = '';
        if ($type === 'photo' && property_exists($message, 'caption') && $message->caption) {
            $text = $message->caption;
        } elseif (property_exists($message, 'text') && $message->text) {
            $text = $message->text;
        }

        $attachment = $type === 'photo' ? "\n\nPhoto is attached." : '';

        return 'You are a content moderator. You are given a ' . $type . ' and you need to determine if it violates the following rules:' . "\n\nIf the " . $type . " violates any of the rules, return 'DELETE' along with the reason in the format 'DELETE: <reason>'." . "\nIf the " . $type . " does not violate any rules, return 'KEEP'." . "\n\nRules:" . implode("\n", config('moderation.rules')) . "\n\n" . ucfirst($type) . ': ' . $text . $attachment . "\nRemember, respond with KEEP or DELETE and nothing else.";
    }

    protected function getPromptPhoto(object $message): string
    {
        return $this->getPrompt($message, 'photo');
    }

    protected function deleteMessage(object $message, ?string $reason = null): void
    {
        $timeout = $this->determineRestrictionTimeout($message->from);

        $text = '';
        if (property_exists($message, 'caption') && $message->caption) {
            $text = $message->caption;
        } elseif (property_exists($message, 'text') && $message->text) {
            $text = "\n\n" . $message->text;
        }

        Telegram::deleteMessage($message->chat->id, $message->message_id);
        Log::info('Message deleted:', ['username' => $message->from->username, 'text' => $text, 'reason' => $reason]);

        $result = Telegram::restrictChatMember($message->chat->id, $message->from->id, time() + $timeout);
        Log::info('User restricted:', ['username' => $message->from->username, 'reason' => $reason, 'result' => $result]);

        $reason = escape_markdown_v2($reason);
        Telegram::sendMessage($message->chat->id, $message->message_thread_id ?? null, "{$message->from->username}, your message was deleted because it violated the following rule: *{$reason}* You're temporarily restricted from sending messages for *{$timeout}* seconds\.");
    }

    protected function determineRestrictionTimeout(object $userInfo): int
    {
        $user = User::firstOrNew(['id' => $userInfo->id], (array) $userInfo);

        // Reset violations if last violation was more than a month ago
        if ($user->last_violation_at && $user->last_violation_at->isPast() && $user->last_violation_at->diffInDays(now()) > 30) {
            $user->violations = 0;
        }

        $user->violations++;
        $user->last_violation_at = now();
        $user->save();

        return 60 * $user->violations; // timeout in seconds
    }

    protected function handleModerationResponse(object $message, string $responseContent): void
    {
        // Parse the response to extract status and reason
        if (str_starts_with($responseContent, 'DELETE')) {
            $parts = explode(': ', $responseContent, 2);
            $status = $parts[0]; // 'DELETE'
            $reason = $parts[1] ?? 'No reason provided'; // Extract reason if available
        } else {
            $status = $responseContent; // Likely 'KEEP' or something else
            $reason = null;
        }

        switch ($status) {
            case 'DELETE':
                $this->deleteMessage($message, $reason);
                break;
            case 'KEEP':
                return;
            default:
                Log::info('Unknown moderation result:', ['response' => $responseContent]);
                break;
        }
    }

    protected function banBots(object $message): bool
    {
        if ($message->from->is_bot) {

            Telegram::banChatMember($message->chat->id, $message->from->id, time() + 86400);

            Telegram::sendMessage($message->chat->id, $message->message_thread_id ?? null, escape_markdown_v2("{$message->from->username} is a bot. Banned."));

            return true;

        }

        return false;
    }

    protected function rateLimit(object $message): bool
    {
        $key = 'moderation:' . $message->from->id;

        // Allow 5 messages per minute per user
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            // Delete the message and notify user they're being rate limited
            Telegram::deleteMessage($message->chat->id, $message->message_id);
            Telegram::sendMessage($message->chat->id, $message->message_thread_id ?? null, escape_markdown_v2("{$message->from->username}, slow down! Please wait {$seconds} seconds before sending more messages."));

            // Temporarily restrict the user for the duration of the rate limit
            Telegram::restrictChatMember($message->chat->id, $message->from->id, time() + $seconds);

            return true;
        }

        RateLimiter::hit($key, 60); // Key expires in 60 seconds
        return false;
    }
}

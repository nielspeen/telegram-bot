<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Telegram
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->token = config('telegram.token');
        $this->apiUrl = config('telegram.api_url') . $this->token;
    }

    public function banChatMember(string $chatId, int $userId, int $untilDate): array
    {
        $response = Http::post($this->apiUrl . '/banChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
        ]);

        return $response->json();
    }

    public function deleteMessage(string $chatId, int $messageId): array
    {
        $response = Http::post($this->apiUrl . '/deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'parse_mode' => 'MarkdownV2',
        ]);

        return $response->json();
    }

    public function getFile(string $fileId, string $fileUniqueId): string
    {
        $response = Http::get($this->apiUrl . '/getFile', [
            'file_id' => $fileId,
        ]);

        // we need to store the file temporarily
        $file = $response->json();

        Log::info('File:', $file);

        $filename = storage_path($fileUniqueId . '.jpg');

        if (!file_exists($filename)) {
            $filePath = $file['result']['file_path'];

            $url = 'https://api.telegram.org/file/bot' . $this->token . '/' . $filePath;

            $response = Http::get($url);

            file_put_contents($filename, $response->body());

            Log::info('File saved to:', ['filename' => $filename]);
        }
        return $filename;
    }

    /**
     * Send a message to a chat
     */
    public function sendMessage(string $chatId, string $text): array
    {
        $response = Http::post($this->apiUrl . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'MarkdownV2',
        ]);

        if ($response->status() !== 200) {
            Log::error('Failed to send message:', $response->json());
        }

        return $response->json();
    }

    /**
     * Get information about the bot
     */
    public function getMe(): array
    {
        $response = Http::get($this->apiUrl . '/getMe');

        return $response->json();
    }

    /**
     * Set the webhook for the bot
     */
    public function setWebhook(string $url): array
    {
        $response = Http::post($this->apiUrl . '/setWebhook', [
            'url' => $url,
        ]);

        return $response->json();
    }

    /**
     * Delete the webhook for the bot
     */
    public function deleteWebhook(): array
    {
        $response = Http::post($this->apiUrl . '/deleteWebhook');

        return $response->json();
    }

    /**
     * Get the webhook info for the bot
     */
    public function getWebhookInfo(): array
    {
        $response = Http::get($this->apiUrl . '/getWebhookInfo');

        return $response->json();
    }

    public function restrictChatMember(string $chatId, int $userId, int $untilDate): array
    {
        $response = Http::post($this->apiUrl . '/restrictChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => [
                'can_send_messages' => false,
                'can_invite_users' => false,
            ],
            'until_date' => $untilDate,
        ]);

        return $response->json();
    }

    public function setMyName(string $name): array
    {
        $response = Http::post($this->apiUrl . '/setMyName', [
            'name' => $name,
            'language_code' => 'en',
        ]);

        return $response->json();
    }
}

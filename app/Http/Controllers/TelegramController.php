<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Facades\Moderator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $object = (object) json_decode($request->getContent());

        Log::info('Telegram webhook request:', (array) $object);

        if (isset($object->message) && property_exists($object->message, 'forum_topic_created')) {
            return response('', 200);
        }

        if (!$this->shouldModerate($object->message)) {
            return response('', 200);
        }

        if (isset($object->message) && property_exists($object->message, 'photo')) {
            Moderator::moderatePhoto($object->message);
        } elseif (isset($object->message)) {
            Moderator::moderate($object->message);
        }

        return response('', 200);
    }

    protected function shouldModerate(object $message): bool
    {
        $user = User::where('username', $message->from->username)->first();

        if ($user && $user->is_unmoderated) {
            return false;
        }

        return true;
    }
}

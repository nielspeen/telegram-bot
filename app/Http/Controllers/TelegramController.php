<?php

namespace App\Http\Controllers;

use App\Facades\Moderator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $object = (object) json_decode($request->getContent());

        Log::info('Telegram webhook request:', (array) $object);

        if (isset($object->message) && property_exists($object->message, 'photo')) {
            Moderator::moderatePhoto($object->message);
        } elseif (isset($object->message)) {
            Moderator::moderate($object->message);
        }

        return response('', 200);
    }
}
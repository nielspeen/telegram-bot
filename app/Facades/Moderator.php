<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $chatId, string $text)
 * @method static array getMe()
 *
 * @see \App\Services\Moderator
 */
class Moderator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'moderator';
    }
}

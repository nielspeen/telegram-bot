<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramGetMe extends Command
{
    protected $signature = 'telegram:get-me';
    protected $description = 'Get Telegram bot information';

    public function handle()
    {
        $response = Telegram::getMe();
        $this->info(json_encode($response, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}

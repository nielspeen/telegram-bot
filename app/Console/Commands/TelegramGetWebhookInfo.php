<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramGetWebhookInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:get-webhook-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the webhook info for the Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = Telegram::getWebhookInfo();
        $this->info(json_encode($result, JSON_PRETTY_PRINT));
    }
}

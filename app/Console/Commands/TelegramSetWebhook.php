<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Telegram;

class TelegramSetWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the webhook for the Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = Telegram::setWebhook(config('telegram.webhook_url') . '/api/telegram/webhook');

        if ($result['ok']) {
            return Command::SUCCESS;
        } else {
            $this->error('Failed to set webhook: ' . $result['description']);
            return Command::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramDeleteWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:delete-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the webhook for the Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = Telegram::deleteWebhook();

        if ($result['ok']) {
            return Command::SUCCESS;
        } else {
            $this->error('Failed to delete webhook: ' . $result['description']);
            return Command::FAILURE;
        }
    }
}

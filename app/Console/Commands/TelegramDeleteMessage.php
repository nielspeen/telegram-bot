<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramDeleteMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:delete-message {chatId} {messageId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = $this->argument('chatId');
        $messageId = $this->argument('messageId');

        $result = Telegram::deleteMessage($chatId, $messageId);

        $this->info(json_encode($result));
    }
}

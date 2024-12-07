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

        if ($result['ok']) {
            $rows = collect($result['result'])->map(fn ($value, $key) => [
                str($key)->title(), $value
            ])->toArray();

            $this->table(['Key', 'Value'], $rows);
        } else {
            $this->error('Error getting webhook info: ' . $result['error_code'] . ' - ' . $result['description']);
        }
    }
}

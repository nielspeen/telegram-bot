<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramSetMyName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-my-name {name}';

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
        $result = Telegram::setMyName($this->argument('name'));
        $this->info(json_encode($result, JSON_PRETTY_PRINT));
    }
}

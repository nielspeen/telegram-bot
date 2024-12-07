<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UsersPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune users stale for more than 1 year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('updated_at', '<', now()->subYear())->delete();
    }
}

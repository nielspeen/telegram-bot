<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserUnmoderate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:unmoderate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exempt a user from moderation.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->ask('Username');

        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $user->is_unmoderated = true;
        $user->save();

        $this->info('User unmoderated');
    }
}

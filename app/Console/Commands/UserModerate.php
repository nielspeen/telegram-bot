<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserModerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:moderate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove moderation exemption from a user.';

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

        $user->is_unmoderated = false;
        $user->save();

        $this->info('User unmoderated');
    }
}

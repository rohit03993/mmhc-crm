<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class SetPassword extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:password {email} {password}';

    /**
     * The console command description.
     */
    protected $description = 'Set password for a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }
        
        $user->update(['password' => Hash::make($password)]);
        
        $this->info("✅ Password updated for user '{$user->name}' ({$user->email})");
        $this->info("   Email: {$user->email}");
        $this->info("   Password: {$password}");
        $this->info("   Role: {$user->role}");
        $this->info("   Unique ID: {$user->unique_id}");
        
        return 0;
    }
}

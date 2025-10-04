<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:admin {email : The email of the user to make admin}';

    /**
     * The console command description.
     */
    protected $description = 'Make a user admin by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }
        
        $user->update(['role' => 'admin']);
        
        $this->info("✅ User '{$user->name}' ({$user->email}) is now an admin!");
        $this->info("   Unique ID: {$user->unique_id}");
        $this->info("   Role: {$user->role}");
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;

class ListAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:all-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users in the CRM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('All Users in MMHC CRM:');
        $this->line('======================');
        
        $users = User::all(['email', 'name', 'unique_id', 'role', 'created_at']);
        
        if ($users->isEmpty()) {
            $this->warn('No users found!');
            return;
        }
        
        foreach ($users as $user) {
            $roleColor = match($user->role) {
                'admin' => 'red',
                'caregiver' => 'yellow',
                'patient' => 'green',
                default => 'white'
            };
            
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->name}");
            $this->line("Unique ID: {$user->unique_id}");
            $this->line("Role: <fg={$roleColor}>{$user->role}</>");
            $this->line("Created: {$user->created_at->format('Y-m-d H:i:s')}");
            $this->line('---');
        }
        
        $this->info("Total users: " . $users->count());
    }
}

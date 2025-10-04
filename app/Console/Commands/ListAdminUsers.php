<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;

class ListAdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:admin-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all admin users in the CRM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Admin Users in MMHC CRM:');
        $this->line('========================');
        
        $adminUsers = User::where('role', 'admin')->get(['email', 'name', 'unique_id', 'created_at']);
        
        if ($adminUsers->isEmpty()) {
            $this->warn('No admin users found!');
            return;
        }
        
        foreach ($adminUsers as $user) {
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->name}");
            $this->line("Unique ID: {$user->unique_id}");
            $this->line("Created: {$user->created_at->format('Y-m-d H:i:s')}");
            $this->line('---');
        }
        
        $this->info("Total admin users: " . $adminUsers->count());
    }
}

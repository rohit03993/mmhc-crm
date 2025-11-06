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
    protected $signature = 'users:list {--role= : Filter by role (admin, nurse, caregiver, patient)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their login credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleFilter = $this->option('role');
        
        $query = User::query();
        
        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }
        
        $users = $query->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'role', 'unique_id', 'is_active', 'created_at']);
        
        if ($users->isEmpty()) {
            $this->warn('No users found!');
            return 0;
        }
        
        // Group by role
        $grouped = $users->groupBy('role');
        
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('                    ALL USERS LOGIN CREDENTIALS');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->line('');
        
        // Admin users
        if ($grouped->has('admin')) {
            $this->info('👑 ADMIN USERS:');
            $this->line('───────────────────────────────────────────────────────────');
            foreach ($grouped->get('admin') as $user) {
                $this->line("Email:     {$user->email}");
                $this->line("Name:       {$user->name}");
                $this->line("Phone:      {$user->phone}");
                $this->line("Unique ID:  {$user->unique_id}");
                $this->line("Password:   password123 (if created by DemoDataSeeder)");
                $this->line("Status:     " . ($user->is_active ? '✅ Active' : '❌ Inactive'));
                $this->line("Created:    {$user->created_at->format('Y-m-d H:i:s')}");
                $this->line('');
            }
        }
        
        // Nurse users
        if ($grouped->has('nurse')) {
            $this->info('👩‍⚕️  NURSE USERS:');
            $this->line('───────────────────────────────────────────────────────────');
            foreach ($grouped->get('nurse') as $user) {
                $this->line("Email:     {$user->email}");
                $this->line("Name:       {$user->name}");
                $this->line("Phone:      {$user->phone}");
                $this->line("Unique ID:  {$user->unique_id}");
                $this->line("Password:   password123 (if created by DemoDataSeeder)");
                $this->line("Status:     " . ($user->is_active ? '✅ Active' : '❌ Inactive'));
                $this->line("Created:    {$user->created_at->format('Y-m-d H:i:s')}");
                $this->line('');
            }
        }
        
        // Caregiver users
        if ($grouped->has('caregiver')) {
            $this->info('👨‍⚕️  CAREGIVER USERS:');
            $this->line('───────────────────────────────────────────────────────────');
            foreach ($grouped->get('caregiver') as $user) {
                $this->line("Email:     {$user->email}");
                $this->line("Name:       {$user->name}");
                $this->line("Phone:      {$user->phone}");
                $this->line("Unique ID:  {$user->unique_id}");
                $this->line("Password:   password123 (if created by DemoDataSeeder)");
                $this->line("Status:     " . ($user->is_active ? '✅ Active' : '❌ Inactive'));
                $this->line("Created:    {$user->created_at->format('Y-m-d H:i:s')}");
                $this->line('');
            }
        }
        
        // Patient users
        if ($grouped->has('patient')) {
            $this->info('👤 PATIENT USERS:');
            $this->line('───────────────────────────────────────────────────────────');
            foreach ($grouped->get('patient') as $user) {
                $this->line("Email:     {$user->email}");
                $this->line("Name:       {$user->name}");
                $this->line("Phone:      {$user->phone}");
                $this->line("Unique ID:  {$user->unique_id}");
                $this->line("Password:   password123 (if created by DemoDataSeeder)");
                $this->line("Status:     " . ($user->is_active ? '✅ Active' : '❌ Inactive'));
                $this->line("Created:    {$user->created_at->format('Y-m-d H:i:s')}");
                $this->line('');
            }
        }
        
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info("Total Users: {$users->count()}");
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->line('');
        $this->comment('Note: All users created by DemoDataSeeder have password: password123');
        $this->comment('If a user was created manually, you may need to reset their password.');
        
        return 0;
    }
}

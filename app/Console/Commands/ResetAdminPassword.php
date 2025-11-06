<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {email? : The admin email to reset password} {--password=password123 : New password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->option('password');

        // If no email provided, get the first admin
        if (!$email) {
            $admin = User::where('role', 'admin')->first();
            if (!$admin) {
                $this->error('No admin user found!');
                return 1;
            }
            $email = $admin->email;
        } else {
            $admin = User::where('email', $email)->where('role', 'admin')->first();
            if (!$admin) {
                $this->error("Admin user with email '{$email}' not found!");
                return 1;
            }
        }

        // Update password
        $admin->password = Hash::make($newPassword);
        $admin->save();

        $this->info("✅ Password reset successfully!");
        $this->line("Email: {$admin->email}");
        $this->line("Name: {$admin->name}");
        $this->line("New Password: {$newPassword}");
        
        return 0;
    }
}


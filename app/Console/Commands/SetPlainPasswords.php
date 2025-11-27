<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class SetPlainPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:set-plain-passwords 
                            {--email= : Set password for specific user by email}
                            {--password= : The plain password to set}
                            {--all-demo : Set known passwords for all demo users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set plain_password field for existing users (for admin password viewing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all-demo')) {
            return $this->setDemoPasswords();
        }

        if ($this->option('email') && $this->option('password')) {
            return $this->setPasswordForUser($this->option('email'), $this->option('password'));
        }

        $this->error('Please provide either --all-demo or --email with --password options.');
        return 1;
    }

    /**
     * Set passwords for demo users
     */
    protected function setDemoPasswords()
    {
        $demoUsers = [
            'mantu@themmhc.com' => 'password123',
            'admin@themmhc.com' => 'password123',
            'nurse@demo.com' => 'password123',
            'caregiver@demo.com' => 'password123',
            'patient@demo.com' => 'password123',
        ];

        $updated = 0;
        $notFound = [];

        foreach ($demoUsers as $email => $password) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $user->plain_password = $password;
                // Also update the hashed password in case it's different
                $user->password = Hash::make($password);
                $user->save();
                
                $this->info("✓ Updated password for: {$email}");
                $updated++;
            } else {
                $notFound[] = $email;
            }
        }

        if (count($notFound) > 0) {
            $this->warn("Users not found: " . implode(', ', $notFound));
        }

        $this->info("\nTotal updated: {$updated}");
        return 0;
    }

    /**
     * Set password for a specific user
     */
    protected function setPasswordForUser($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $user->plain_password = $password;
        $user->password = Hash::make($password);
        $user->save();

        $this->info("✓ Updated password for: {$user->name} ({$email})");
        return 0;
    }
}


<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Facades\Hash;

class ResetDemoPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset passwords for demo users to password123';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting passwords for demo users...');
        
        $users = [
            'mantu@themmhc.com',
            'nurse@demo.com',
            'caregiver@demo.com',
            'patient@demo.com',
        ];
        
        $password = 'password123';
        
        foreach ($users as $email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                // Use direct DB update to bypass any casting issues
                \DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make($password)]);
                
                $this->info("✓ Reset password for: {$email} ({$user->role})");
            } else {
                $this->warn("✗ User not found: {$email}");
            }
        }
        
        $this->info('');
        $this->info('All demo passwords reset to: password123');
        $this->info('');
        $this->info('Login Credentials:');
        $this->info('Admin: mantu@themmhc.com / password123');
        $this->info('Nurse: nurse@demo.com / password123');
        $this->info('Caregiver: caregiver@demo.com / password123');
        $this->info('Patient: patient@demo.com / password123');
        
        return 0;
    }
}


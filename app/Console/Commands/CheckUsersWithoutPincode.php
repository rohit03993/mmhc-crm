<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use Illuminate\Support\Str;

class CheckUsersWithoutPincode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-missing-pincodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show users without pincode data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('pincode')
            ->get(['id', 'name', 'email', 'role', 'address', 'pincode']);
        
        if ($users->isEmpty()) {
            $this->info('✅ All users have pincode data!');
            return 0;
        }
        
        $this->warn("⚠️ Found {$users->count()} user(s) without pincode:");
        $this->newLine();
        
        $tableData = [];
        foreach ($users as $user) {
            $tableData[] = [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Role' => $user->role,
                'Address' => $user->address ? Str::limit($user->address, 50) : 'No address',
            ];
        }
        
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Address'],
            $tableData
        );
        
        $this->newLine();
        $this->info('💡 To fix:');
        $this->info('   1. Update the address to include a valid pincode');
        $this->info('   2. Run: php artisan users:extract-pincodes');
        $this->info('   3. Or manually update via admin panel');
        
        return 0;
    }
}


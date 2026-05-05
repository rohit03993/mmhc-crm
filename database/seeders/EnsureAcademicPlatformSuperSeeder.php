<?php

namespace Database\Seeders;

use App\Models\Core\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Ensures the academics platform super_admin exists (multi-college demos do not create this user).
 * Password matches other academic demos: password123
 */
class EnsureAcademicPlatformSuperSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password123';
        $defaultLocation = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");

        $superAdmin = User::firstOrCreate(
            ['email' => 'academic.super@themmhc.com'],
            [
                'name' => 'Vikram Joshi',
                'phone' => '9825511001',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'unique_id' => 'ACAD-SA-001',
                'is_active' => true,
                'email_verified_at' => now(),
                'location' => $defaultLocation,
            ]
        );
        DB::table('users')->where('id', $superAdmin->id)->update([
            'password' => Hash::make($password),
            'role' => 'super_admin',
        ]);

        $this->command?->info('Platform academics super_admin ready: academic.super@themmhc.com / '.$password);
    }
}

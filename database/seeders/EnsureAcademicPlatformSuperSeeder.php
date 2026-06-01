<?php

namespace Database\Seeders;

use App\Models\Core\User;
use Illuminate\Database\Seeder;

/**
 * @deprecated super_admin role removed — platform academics uses CRM admin + institute_admin per college.
 */
class EnsureAcademicPlatformSuperSeeder extends Seeder
{
    public function run(): void
    {
        $remaining = User::withTrashed()->where('role', 'super_admin')->count();
        if ($remaining > 0) {
            $this->command?->warn(
                "{$remaining} legacy super_admin user(s) still exist. Run: php artisan academics:purge-super-admin-users"
            );
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Encrypts all existing plain_password values in the users table
     */
    public function up(): void
    {
        // Check if plain_password column exists
        if (!Schema::hasColumn('users', 'plain_password')) {
            Log::warning('plain_password column does not exist in users table. Skipping encryption migration.');
            return;
        }

        // Get all users with non-null plain_password
        $users = DB::table('users')
            ->whereNotNull('plain_password')
            ->where('plain_password', '!=', '')
            ->get(['id', 'plain_password']);

        $encrypted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                $plainPassword = $user->plain_password;
                
                // Check if already encrypted (encrypted strings are typically long base64 strings > 60 chars)
                // Also check if it can be base64 decoded (encrypted strings are base64 encoded)
                $isEncrypted = strlen($plainPassword) > 60 && base64_decode($plainPassword, true) !== false;
                
                if ($isEncrypted) {
                    // Try to decrypt to verify it's properly encrypted
                    try {
                        Crypt::decryptString($plainPassword);
                        // Already encrypted, skip
                        $skipped++;
                        continue;
                    } catch (\Exception $e) {
                        // Decryption failed, might be old format - encrypt it
                        // Continue to encryption logic below
                    }
                }

                // Encrypt the password
                try {
                    $encryptedPassword = Crypt::encryptString($plainPassword);
                    
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['plain_password' => $encryptedPassword]);
                    
                    $encrypted++;
                } catch (\Exception $e) {
                    Log::error("Failed to encrypt password for user {$user->id}: " . $e->getMessage());
                    $errors++;
                }
            } catch (\Exception $e) {
                Log::error("Error processing user {$user->id}: " . $e->getMessage());
                $errors++;
            }
        }

        // Log results
        Log::info("Password encryption migration completed: {$encrypted} encrypted, {$skipped} skipped, {$errors} errors");
    }

    /**
     * Reverse the migrations.
     * NOTE: Cannot decrypt passwords as encryption is one-way in practice
     * This will set all encrypted passwords to null
     */
    public function down(): void
    {
        // WARNING: This will clear all encrypted passwords
        // We cannot decrypt them back to plain text securely
        // Only run this if you're sure you want to lose password visibility
        
        if (!Schema::hasColumn('users', 'plain_password')) {
            return;
        }

        // Set all encrypted passwords to null
        // This is irreversible - passwords will need to be reset manually
        DB::table('users')
            ->whereNotNull('plain_password')
            ->update(['plain_password' => null]);
        
        Log::warning('Password encryption migration rolled back. All plain_password values have been cleared.');
    }
};

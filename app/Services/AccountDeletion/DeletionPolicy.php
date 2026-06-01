<?php

namespace App\Services\AccountDeletion;

use App\Models\Core\User;
use Illuminate\Validation\ValidationException;

class DeletionPolicy
{
    public function assertDeletable(User $target, User $actor): void
    {
        if ($target->trashed()) {
            throw ValidationException::withMessages([
                'user' => 'This account is already removed.',
            ]);
        }

        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account from here.',
            ]);
        }

        if (! $actor->isAdmin()) {
            throw ValidationException::withMessages([
                'user' => 'Only CRM administrators can delete accounts.',
            ]);
        }

        if (in_array($target->role, User::protectedFromBulkUserDeletionRoleSlugs(), true)) {
            throw ValidationException::withMessages([
                'user' => 'This account role is protected and cannot be deleted.',
            ]);
        }

        if ($target->role === 'admin' && $this->isLastCrmAdmin($target)) {
            throw ValidationException::withMessages([
                'user' => 'Cannot delete the last CRM admin account.',
            ]);
        }
    }

    public function canSelectForBulkDelete(User $target, User $actor): bool
    {
        if ($target->trashed()) {
            return false;
        }

        if ($actor->id === $target->id) {
            return false;
        }

        if (in_array($target->role, User::protectedFromBulkUserDeletionRoleSlugs(), true)) {
            return false;
        }

        if ($target->role === 'admin' && $this->isLastCrmAdmin($target)) {
            return false;
        }

        return true;
    }

    private function isLastCrmAdmin(User $target): bool
    {
        if ($target->role !== 'admin') {
            return false;
        }

        return User::query()
            ->where('role', 'admin')
            ->where('id', '!=', $target->id)
            ->whereNull('deleted_at')
            ->doesntExist();
    }
}

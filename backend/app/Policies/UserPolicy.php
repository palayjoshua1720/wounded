<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->user_role, [0, 1, 3]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return $model->user_role !== 0;
        }
        
        if ($user->user_role === 3) {
            return $model->user_role === 3;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->user_role, [0, 1, 3]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Cannot update archived or soft deleted users
        if ($model->user_status === 2 || $model->trashed()) {
            return false;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return $model->user_role !== 0;
        }
        
        if ($user->user_role === 3) {
            return $model->user_role === 3;
        }
        
        return false;
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, User $model): bool
    {
        // Cannot archive yourself, archived users, or soft deleted users
        if ($user->id === $model->id || $model->user_status === 2 || $model->trashed()) {
            return false;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return $model->user_role !== 0;
        }
        
        if ($user->user_role === 3) {
            return $model->user_role === 3;
        }
        
        return false;
    }

    /**
     * Determine whether the user can soft delete the model.
     */
    public function softDelete(User $user, User $model): bool
    {
        // Can only soft delete archived users, not yourself, and not already soft deleted
        if ($user->id === $model->id || $model->user_status !== 2 || $model->trashed()) {
            return false;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return $model->user_role !== 0;
        }
        
        if ($user->user_role === 3) {
            return $model->user_role === 3;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore soft deleted models.
     */
    public function restore(User $user, User $model): bool
    {
        // Can only restore soft deleted users
        if (!$model->trashed()) {
            return false;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return $model->user_role !== 0;
        }
        
        if ($user->user_role === 3) {
            return $model->user_role === 3;
        }
        
        return false;
    }

    /**
     * Determine allowed roles for user creation based on current user's role
     */
    public function getAllowedRolesForCreation(User $user): array
    {
        switch ($user->user_role) {
            case 0: // Admin - can create all roles
                return [0, 1, 2, 3, 4, 5];
            case 1: // Office Staff - can create all except admin
                return [1, 2, 3, 4, 5];
            case 3: // Clinician - can only create clinicians
                return [3];
            default:
                return [];
        }
    }

    /**
     * Determine whether the user can view users of specific role
     */
    public function canViewRole(User $user, int $targetRole): bool
    {
        switch ($user->user_role) {
            case 0: // Admin - can view all roles
                return true;
            case 1: // Office Staff - can view all except admin (role 0)
                return $targetRole !== 0;
            case 3: // Clinician - can only view clinicians (role 3)
                return $targetRole === 3;
            default:
                return false;
        }
    }
}
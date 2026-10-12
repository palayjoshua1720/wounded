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
        return in_array($user->user_role, [0, 1, 2]);
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
            return !in_array($model->user_role, [0, 1], true);
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->user_role, [0, 1, 2]);
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

        // Users can always update their own account profile (role/status escalation is rejected in controller)
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return !in_array($model->user_role, [0, 1], true);
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can toggle status of the model.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        // Cannot toggle yourself or soft deleted users
        if ($user->id === $model->id || $model->trashed()) {
            return false;
        }

        if ($user->user_role === 0) {
            return true;
        }
        
        if ($user->user_role === 1) {
            return !in_array($model->user_role, [0, 1], true);
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
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
            return !in_array($model->user_role, [0, 1], true);
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
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
            return false;
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
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
            return false;
        }
        
        if ($user->user_role === 2) {
            return $model->user_role === 3 && (int) $model->clinic_id === (int) $user->clinic_id;
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
            case 1: // Office Staff - cannot create admin (0) or office staff (1)
                return [2, 3, 4, 5];
            case 2: // Clinic User - can create clinicians
                return [3];
            default:
                return [];
        }
    }

    /**
     * Determine allowed roles for user update based on current user's role.
     * Enforces the HIPAA / RBAC hierarchy: Office Staff, Clinic, and Clinician
     * cannot assign any role higher than or equal to their current restricted level.
     */
    public function getAllowedRolesForUpdate(User $currentUser, User $targetUser): array
    {
        switch ($currentUser->user_role) {
            case 0: // Admin - can assign all roles
                return [0, 1, 2, 3, 4, 5];
            case 1: // Office Staff - cannot assign admin (0) or office staff (1)
                return [2, 3, 4, 5];
            case 2: // Clinic User - can only manage clinicians (cannot assign 0, 1, or 2)
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
            case 1: // Office Staff - can view partner facilities (clinics, clinicians, manufacturers, billers), not internal admin/staff
                return !in_array($targetRole, [0, 1], true);
            case 2: // Clinic User - can view clinicians
                return $targetRole === 3;
            default:
                return false;
        }
    }
}
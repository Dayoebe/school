<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, $role = null)
    {
        if ($role && $user->can("read $role")) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model, $role = null)
    {
        if ($user->school_id != $model->school_id) {
            return false;
        }

        // Try to get role from model if not provided
        $role = $role ?: $model->getRoleNames()->first();
        
        if ($role && $user->can("read $role") && $user->school_id == $model->school_id) {
            return true;
        }
        
        // user can view his own profile
        return $user->id == $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $role = null)
    {
        if ($role && $user->can("create $role")) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model, $role = null)
    {
        if ($user->school_id != $model->school_id) {
            return false;
        }

        // Try to get role from model if not provided
        $role = $role ?: $model->getRoleNames()->first();
        
        return $role && $user->can("update $role") && $user->school_id == $model->school_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model, $role = null)
    {
        if ($user->school_id != $model->school_id) {
            return false;
        }

        // Try to get role from model if not provided
        $role = $role ?: $model->getRoleNames()->first();
        
        return $role && $user->can("delete $role") && $user->school_id == $model->school_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }

    public function lockAccount(User $user, User $model)
    {
        return $user->can('lock user account') && $model->school_id == $user->school_id;
    }
}
<?php

namespace App\Policies;

use App\Models\Rent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::IS_ADMIN, User::IS_OWNER, User::IS_USER]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Rent $rent): bool
    {
        return $user->id === $rent->user_id || ($user->role === User::IS_OWNER && $user->id === $rent->building->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === User::IS_USER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rent $rent): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rent $rent): bool
    {
        return $user->id === $rent->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rent $rent): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rent $rent): bool
    {
        //
    }
}

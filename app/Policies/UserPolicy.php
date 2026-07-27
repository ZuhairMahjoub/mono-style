<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->role==='admin'){
            return Response::allow();
        }
        return Response::deny('Only admin can do that');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model)
    {
        if($user->role==='admin'){
         return Response::allow();
        }
        if($user->id===$model->id){
         return Response::allow();
        }
        return Response::deny('You are not allowed to view this profile');
    
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model)
    {
        if($user->role==='admin'){
         return Response::allow();
        }
        if($user->id===$model->id){
         return Response::allow();
        }
        return Response::deny('You are not allowed to update this profile');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model)
    {
        if($user->role==='admin'){
         return Response::allow();
        }
        if($user->id===$model->id){
         return Response::allow();
        }
        return Response::deny('You are not allowed to delete this profile');
    
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}

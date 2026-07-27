<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->role==='admin'){
            return Response::allow();
        }
        return Response::deny('Only admins can do that');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order)
    {
        if($user->role === 'admin'|| ($user->role === 'user' && $user->id === $order->user_id)){
        return Response::allow();
      }
        return Response::deny('You are not allowed to view this order');
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
    public function update(User $user, Order $order)
    {
      if($user->role === 'admin'|| ($user->role === 'user' && $user->id === $order->user_id)){
        return Response::allow();
      }
        return Response::deny('You are not allowed to update this order');
    }
    

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order)
{
    if($user->role === 'admin'|| ($user->role === 'user' && $user->id === $order->user_id)){
        return Response::allow();
      }
        return Response::deny('You are not allowed to delete this order');
    }



    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}

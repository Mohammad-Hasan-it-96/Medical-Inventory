<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * OrderPolicy
 *
 * Web dashboard  — all actions require admin or moderator.
 * API (Flutter)  — reps may view/confirm/cancel their OWN orders (rep_id match);
 *                  admin and moderator may act on any order.
 */
class OrderPolicy
{
    use HandlesAuthorization;

    /** Admin/mod sees all; rep sees only orders where they are the assigned rep. */
    public function view(User $user, Order $order): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->role === 'rep' && $order->rep_id === $user->id;
    }

    /** Admin/mod can confirm any order; rep can confirm orders assigned to them. */
    public function confirm(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    /** Admin/mod can cancel any order; rep can cancel orders assigned to them. */
    public function cancel(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    /** Only admin and moderator may create orders via the web dashboard. */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }
}


<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * PaymentPolicy
 *
 * Web dashboard  — only admin/moderator may record or list payments.
 * API (Flutter)  — reps may record a payment only for a pharmacy assigned to them;
 *                  listing is scoped in the controller (no policy viewAny needed).
 */
class PaymentPolicy
{
    use HandlesAuthorization;

    /** Admin/mod may view the payments list in the dashboard; reps are excluded. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /** Admin/mod may create any payment; rep may only create for their own pharmacy. */
    public function createForPharmacy(User $user, Pharmacy $pharmacy): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->role === 'rep' && $pharmacy->rep_id === $user->id;
    }
}


<?php

namespace App\Policies;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * PharmacyPolicy
 *
 * Web dashboard  — CUD actions require admin or moderator.
 * API (Flutter)  — rep users may only view/access pharmacies assigned to them
 *                  (rep_id match); admin and moderator have full access.
 */
class PharmacyPolicy
{
    use HandlesAuthorization;

    /** Admin/mod sees all; rep only sees their assigned pharmacies. */
    public function view(User $user, Pharmacy $pharmacy): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->role === 'rep' && $pharmacy->rep_id === $user->id;
    }

    /** Only admin and moderator may create pharmacies. */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /** Only admin and moderator may update pharmacies. */
    public function update(User $user, Pharmacy $pharmacy): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /** Only admin may delete pharmacies. */
    public function delete(User $user, Pharmacy $pharmacy): bool
    {
        return $user->role === 'admin';
    }
}


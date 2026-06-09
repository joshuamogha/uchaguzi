<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;

class ElectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function exportResults(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function viewResults(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function manageResults(User $user, Election $election): bool
    {
        return $user->isAdmin();
    }

    public function enterManualBallots(User $user, Election $election): bool
    {
        return true;
    }
}

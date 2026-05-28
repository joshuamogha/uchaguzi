<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;

class ElectionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Election $election): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Election $election): bool
    {
        return true;
    }

    public function delete(User $user, Election $election): bool
    {
        return true;
    }

    public function exportResults(User $user, Election $election): bool
    {
        return true;
    }
}

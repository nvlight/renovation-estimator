<?php

namespace App\Policies;

use App\Models\RoomJob;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class RoomJobPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoomJob $roomJob): bool
    {
        return $user->id === $roomJob->room->project->user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RoomJob $roomJob): bool
    {
        return $user->id === $roomJob->room->project->user->id;
    }
}

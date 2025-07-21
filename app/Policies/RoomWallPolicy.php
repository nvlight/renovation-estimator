<?php

namespace App\Policies;

use App\Models\RoomWall;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoomWallPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RoomWall $roomWall): bool
    {
        return $user->id === $roomWall->room->project->user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RoomWall $roomWall): bool
    {
        return $user->id === $roomWall->room->project->user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RoomWall $roomWall): bool
    {
        return $user->id === $roomWall->room->project->user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RoomWall $roomWall): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RoomWall $roomWall): bool
    {
        return false;
    }
}

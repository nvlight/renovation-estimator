<?php

namespace App\Policies;

use App\Models\RoomMaterial;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoomMaterialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, $room): bool
    {
        return $room->project->user->id === $user->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RoomMaterial $roomMaterial): bool
    {
        return false;
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
    public function update(User $user, RoomMaterial $roomMaterial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RoomMaterial $roomMaterial): bool
    {
        return $user->id === $roomMaterial->room->project->user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RoomMaterial $roomMaterial): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RoomMaterial $roomMaterial): bool
    {
        return false;
    }
}

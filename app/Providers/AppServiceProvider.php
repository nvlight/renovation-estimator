<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Room;
use App\Policies\ProjectPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
    }
}

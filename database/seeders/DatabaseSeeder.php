<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->resetBusinessTables();
        $this->clearSeededMaterialImages();

        $this->call([
            UserSeeder::class,
            ProjectSeeder::class,
            RoomSeeder::class,
            RoomWallSeeder::class,
            MaterialSeeder::class,
            RoomJobsSeeder::class,
            RoomMaterialSeeder::class,
        ]);
    }

    private function resetBusinessTables(): void
    {
        DB::statement('TRUNCATE TABLE
            room_materials,
            material_images,
            room_jobs,
            room_walls,
            rooms,
            projects,
            materials,
            users,
            personal_access_tokens,
            password_reset_tokens,
            sessions
            RESTART IDENTITY CASCADE');
    }

    private function clearSeededMaterialImages(): void
    {
        $dir = storage_path('app/public/materials');
        if (! File::isDirectory($dir)) {
            return;
        }

        foreach (File::glob($dir.'/seed-*') ?: [] as $file) {
            File::delete($file);
        }
    }
}

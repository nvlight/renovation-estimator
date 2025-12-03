<?php

namespace Database\Seeders;

use App\Models\RoomMaterial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sail artisan db:seed --class=RoomMaterialSeeder
        RoomMaterial::factory(10)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (DemoDataset::users() as $user) {
            User::factory()->create([
                'name' => $user['name'],
                'email' => $user['email'],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialImage;
use Database\Seeders\Support\MaterialCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (MaterialCatalog::all() as $item) {
            Material::query()->create($item);
        }

        Storage::disk('public')->makeDirectory('materials');

        $fixturesDir = database_path('seeders/fixtures/materials');

        foreach (MaterialCatalog::imageFixtures() as $materialId => $files) {
            foreach ($files as $index => $filename) {
                $source = $fixturesDir.'/'.$filename;
                if (! File::exists($source)) {
                    throw new \RuntimeException("Material fixture is missing: {$source}");
                }

                $dest = 'materials/seed-'.$materialId.'-'.($index + 1).'.jpg';
                Storage::disk('public')->put($dest, File::get($source));

                MaterialImage::query()->create([
                    'material_id' => $materialId,
                    'name' => $dest,
                ]);
            }
        }
    }
}

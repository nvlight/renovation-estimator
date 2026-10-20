<?php

use App\Http\Resources\V1\MaterialImageResource;
use App\Models\MaterialImage;
use Illuminate\Http\Request;

it('builds a public image URL without duplicating the application port', function () {
    config(['filesystems.disks.public.url' => 'http://localhost:8008/storage']);

    $image = new MaterialImage([
        'name' => 'materials/seed-37-1.jpg',
    ]);

    $data = (new MaterialImageResource($image))->toArray(Request::create('/api/v1/material/37'));

    expect($data['url'])->toBe('http://localhost:8008/storage/materials/seed-37-1.jpg')
        ->not->toContain(':8008:8008');
});

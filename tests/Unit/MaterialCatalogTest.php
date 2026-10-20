<?php

use Database\Seeders\Support\MaterialCatalog;

it('contains the Satentek putty and its image fixture', function () {
    $catalog = MaterialCatalog::all();
    $satentek = $catalog[36];
    $fixture = __DIR__.'/../../database/seeders/fixtures/materials/satentek.jpg';
    $imageSize = getimagesize($fixture);

    expect($catalog)->toHaveCount(37)
        ->and($satentek['title'])->toBe('Шпаклевка Сатентек 20 кг')
        ->and($satentek['product_code'])->toBe(189676979)
        ->and($satentek['price'])->toBe(875.0)
        ->and($satentek['brand'])->toBe('Satentek')
        ->and($satentek['producing_country'])->toBe('Турция')
        ->and(MaterialCatalog::imageFixtures()[37])->toBe(['satentek.jpg'])
        ->and($fixture)->toBeFile()
        ->and(exif_imagetype($fixture))->toBe(IMAGETYPE_JPEG)
        ->and($imageSize[0])->toBeLessThanOrEqual(1200)
        ->and($imageSize[1])->toBeLessThanOrEqual(925);
});

<?php

use App\Services\Maps\HexGridGenerator;

test('pointy top polygon coordinates are generated around the centre', function () {
    $polygon = (new HexGridGenerator())->polygon(100, 100, 25, 'pointy-top');

    expect($polygon)->toHaveCount(6);
    expect($polygon[0]['x'])->toBe(121.651);
    expect($polygon[0]['y'])->toBe(87.5);
});

test('flat top polygon coordinates are generated around the centre', function () {
    $polygon = (new HexGridGenerator())->polygon(100, 100, 25, 'flat-top');

    expect($polygon)->toHaveCount(6);
    expect($polygon[0]['x'])->toBe(125.0);
    expect($polygon[0]['y'])->toBe(100.0);
});

test('pointy top grid spacing offsets alternating rows', function () {
    $hexes = (new HexGridGenerator())->generate(120, 100, 20, 'pointy-top');

    expect($hexes[0]['centre_y'])->toBe(20.0);
    expect($hexes[1]['centre_y'])->toBe(20.0);

    $secondRow = collect($hexes)->firstWhere('grid_row', 1);

    expect($secondRow['centre_y'])->toBe(50.0);
    expect($secondRow['centre_x'])->toBe(round(20 + ((sqrt(3) * 20) / 2), 3));
});

test('flat top grid spacing offsets alternating columns', function () {
    $hexes = (new HexGridGenerator())->generate(120, 100, 20, 'flat-top');
    $secondColumn = collect($hexes)->firstWhere('grid_column', 1);

    expect($secondColumn['centre_x'])->toBe(50.0);
    expect($secondColumn['centre_y'])->toBe(round(20 + ((sqrt(3) * 20) / 2), 3));
});

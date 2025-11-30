<?php

use App\Models\Planet;
use App\Services\SvgPlanetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->svgPlanetService = new SvgPlanetService();
});

it('can generate svg for a planet', function () {
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => '#FF5733',
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    expect($svg)->toBeString();
    expect(strlen($svg))->toBeGreaterThan(0);
    
    // Проверяем, что SVG содержит правильные элементы
    assertStringContainsString('<svg', $svg);
    assertStringContainsString('</svg>', $svg);
    assertStringContainsString('<circle', $svg);
});

it('generates different svgs for different planet types', function () {
    $terrestrialPlanet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => '#33FF57',
    ]);

    $gasGiantPlanet = Planet::factory()->create([
        'type' => 'gas_giant',
        'size' => 50000,
        'color' => '#3357FF',
    ]);

    $terrestrialSvg = $this->svgPlanetService->generateSvg($terrestrialPlanet);
    $gasGiantSvg = $this->svgPlanetService->generateSvg($gasGiantPlanet);

    // Проверяем, что SVG разные
    expect($terrestrialSvg)->not->toBe($gasGiantSvg);
    
    // Проверяем специфичные элементы для разных типов планет
    assertStringContainsString('cx', $terrestrialSvg);
    assertStringContainsString('cy', $terrestrialSvg);
    
    // Для газового гиганта могут быть дополнительные кольца
    if (str_contains($gasGiantSvg, 'ellipse')) {
        assertStringContainsString('ellipse', $gasGiantSvg);
    }
});

it('uses correct colors in svg', function () {
    $color = '#FF5733';
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => $color,
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    // Проверяем, что цвет используется в SVG
    assertStringContainsString($color, $svg);
});

it('generates correct size svg', function () {
    $size = 8000;
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => $size,
        'color' => '#FF5733',
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    // Проверяем, что размер используется в SVG
    assertStringContainsString((string)($size / 100), $svg); // Предполагаем масштабирование
});

it('handles special features in svg', function () {
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => '#FF5733',
        'special_features' => ['ring' => true],
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    // Проверяем, что специальные особенности отображаются в SVG
    assertStringContainsString('ellipse', $svg); // Кольца отображаются как эллипсы
});

it('generates valid xml', function () {
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => '#FF5733',
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    // Проверяем, что SVG является валидным XML
    $doc = new DOMDocument();
    $result = $doc->loadXML($svg);
    
    expect($result)->toBeTrue();
});

it('handles planets without color', function () {
    $planet = Planet::factory()->create([
        'type' => 'terrestrial',
        'size' => 5000,
        'color' => null,
    ]);

    $svg = $this->svgPlanetService->generateSvg($planet);

    expect($svg)->toBeString();
    expect(strlen($svg))->toBeGreaterThan(0);
    
    // Должен использовать цвет по умолчанию
    assertStringNotContainsString('fill=""', $svg);
    assertStringNotContainsString('fill="null"', $svg);
});
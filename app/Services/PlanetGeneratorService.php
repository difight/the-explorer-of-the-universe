<?php

namespace App\Services;

use App\Models\Planet;
use App\Models\StarSystem;
use Illuminate\Support\Str;

class PlanetGeneratorService
{
    private array $planetTypes = [
        [
            'name' => 'barren',
            'life_chance' => 0.0,
            'resource_bonus' => 1.0,
            'rarity' => 1.0,
            'temp_range' => [-200, 200],
            'size_range' => [1000, 8000],
            'color' => 'gray'
        ],
        [
            'name' => 'desert',
            'life_chance' => 0.001,
            'resource_bonus' => 1.2,
            'rarity' => 1.5,
            'temp_range' => [-50, 80],
            'size_range' => [4000, 12000],
            'color' => 'yellow'
        ],
        [
            'name' => 'oceanic',
            'life_chance' => 0.01,
            'resource_bonus' => 1.5,
            'rarity' => 2.0,
            'temp_range' => [-20, 40],
            'size_range' => [8000, 15000],
            'color' => 'blue'
        ],
        [
            'name' => 'tundra',
            'life_chance' => 0.003,
            'resource_bonus' => 1.1,
            'rarity' => 1.8,
            'temp_range' => [-80, 10],
            'size_range' => [5000, 10000],
            'color' => 'white'
        ],
        [
            'name' => 'gas_giant',
            'life_chance' => 0.0,
            'resource_bonus' => 2.0,
            'rarity' => 1.2,
            'temp_range' => [-180, -80],
            'size_range' => [50000, 150000],
            'color' => 'orange'
        ],
        [
            'name' => 'volcanic',
            'life_chance' => 0.0001,
            'resource_bonus' => 1.8,
            'rarity' => 2.5,
            'temp_range' => [200, 500],
            'size_range' => [6000, 10000],
            'color' => 'red'
        ],
        [
            'name' => 'ice_giant',
            'life_chance' => 0.0,
            'resource_bonus' => 1.7,
            'rarity' => 1.7,
            'temp_range' => [-220, -150],
            'size_range' => [30000, 60000],
            'color' => 'light-blue'
        ],
        [
            'name' => 'jungle',
            'life_chance' => 0.02,
            'resource_bonus' => 1.6,
            'rarity' => 3.0,
            'temp_range' => [15, 35],
            'size_range' => [8000, 13000],
            'color' => 'green'
        ],
        [
            'name' => 'toxic',
            'life_chance' => 0.00001,
            'resource_bonus' => 1.4,
            'rarity' => 2.2,
            'temp_range' => [-100, 100],
            'size_range' => [7000, 11000],
            'color' => 'purple'
        ],
        [
            'name' => 'terrestrial',
            'life_chance' => 0.005,
            'resource_bonus' => 1.3,
            'rarity' => 2.0,
            'temp_range' => [-30, 30],
            'size_range' => [8000, 14000],
            'color' => 'brown'
        ],
        [
            'name' => 'crystal',
            'life_chance' => 0.0005,
            'resource_bonus' => 2.2,
            'rarity' => 3.5,
            'temp_range' => [-100, 50],
            'size_range' => [3000, 7000],
            'color' => 'pink'
        ],
        [
            'name' => 'swamp',
            'life_chance' => 0.008,
            'resource_bonus' => 1.4,
            'rarity' => 2.8,
            'temp_range' => [10, 30],
            'size_range' => [9000, 12000],
            'color' => 'dark-green'
        ]
    ];

    private array $specialFeatures = [
        'rings' => ['rarity' => 0.1, 'types' => ['gas_giant', 'ice_giant']],
        'strong_magnetic_field' => ['rarity' => 0.2, 'types' => ['terrestrial', 'barren']],
        'volcanic_activity' => ['rarity' => 0.3, 'types' => ['volcanic', 'terrestrial']],
        'cryo_volcanoes' => ['rarity' => 0.15, 'types' => ['tundra', 'ice_giant']],
        'methane_lakes' => ['rarity' => 0.1, 'types' => ['toxic', 'ice_giant']],
        'crystal_formations' => ['rarity' => 0.25, 'types' => ['crystal', 'barren']],
        'ancient_ruins' => ['rarity' => 0.05, 'types' => ['terrestrial', 'jungle', 'desert']],
        'quantum_anomaly' => ['rarity' => 0.02, 'types' => ['all']],
        'floating_islands' => ['rarity' => 0.08, 'types' => ['gas_giant', 'toxic']],
        'subsurface_ocean' => ['rarity' => 0.2, 'types' => ['ice_giant', 'tundra', 'barren']],
        'aurora_borealis' => ['rarity' => 0.15, 'types' => ['tundra', 'ice_giant']],
        'acid_rains' => ['rarity' => 0.1, 'types' => ['toxic', 'volcanic']],
        'bioluminescent_flora' => ['rarity' => 0.1, 'types' => ['jungle', 'swamp']],
        'geothermal_vents' => ['rarity' => 0.2, 'types' => ['oceanic', 'volcanic']],
        'sandstorms' => ['rarity' => 0.3, 'types' => ['desert', 'barren']],
    ];

    private array $starTypes = [
        'M' => ['mass_range' => [0.08, 0.45], 'temp_range' => [2400, 3700], 'luminosity' => [0.01, 0.1]],
        'K' => ['mass_range' => [0.45, 0.8], 'temp_range' => [3700, 5200], 'luminosity' => [0.1, 0.4]],
        'G' => ['mass_range' => [0.8, 1.04], 'temp_range' => [5200, 6000], 'luminosity' => [0.6, 1.1]],
        'F' => ['mass_range' => [1.04, 1.4], 'temp_range' => [6000, 7500], 'luminosity' => [1.5, 3.0]],
        'A' => ['mass_range' => [1.4, 2.1], 'temp_range' => [7500, 10000], 'luminosity' => [5, 25]],
        'B' => ['mass_range' => [2.1, 16], 'temp_range' => [10000, 30000], 'luminosity' => [25, 30000]],
    ];

    public function generateForSystem(StarSystem $system): void
    {
        if ($system->is_generated) {
            return;
        }

        if (!$system->is_start_system) {
            $this->generateStar($system);
        }

        $seed = $this->createSeed($system->coord_x, $system->coord_y, $system->coord_z);
        mt_srand($seed);

        $numPlanets = mt_rand(2, 8);

        for ($i = 0; $i < $numPlanets; $i++) {
            $this->generatePlanet($system, $i, $seed + $i);
        }

        $system->update(['is_generated' => true]);
    }

    private function generateStar(StarSystem $system): void
    {
        $seed = $this->createSeed($system->coord_x, $system->coord_y, $system->coord_z);
        mt_srand($seed);

        $starWeights = [70, 25, 4, 0.5, 0.3, 0.2];
        $starTypes = array_keys($this->starTypes);

        $randomValue = mt_rand() / mt_getrandmax() * array_sum($starWeights);
        $cumulative = 0;
        $selectedType = 'M';

        foreach ($starWeights as $index => $weight) {
            $cumulative += $weight;
            if ($randomValue <= $cumulative) {
                $selectedType = $starTypes[$index];
                break;
            }
        }

        $starConfig = $this->starTypes[$selectedType];
        $mass = $starConfig['mass_range'][0] + (mt_rand() / mt_getrandmax()) * ($starConfig['mass_range'][1] - $starConfig['mass_range'][0]);

        $system->update([
            'star_type' => $selectedType,
            'star_mass' => round($mass, 2)
        ]);
    }

    private function generatePlanet(StarSystem $system, int $orbitNumber, int $seed): void
    {
        mt_srand($seed);

        $planetType = $this->selectPlanetType();
        $hasLife = $this->checkForLife($planetType, $orbitNumber, $system->star_type);

        // Более реалистичная температура на основе орбиты и типа звезды
        $temperature = $this->calculateRealisticTemperature($system, $orbitNumber, $planetType);

        // Размер в пределах диапазона типа планеты
        $size = mt_rand($planetType['size_range'][0], $planetType['size_range'][1]);

        $specialFeatures = $this->generateSpecialFeatures($planetType['name']);

        Planet::create([
            'star_system_id' => $system->id,
            'tech_name' => "Planet-" . ($orbitNumber + 1),
            'type' => $planetType['name'],
            'has_life' => $hasLife,
            'size' => $size,
            'resource_bonus' => $planetType['resource_bonus'] * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4),
            'special_features' => $specialFeatures,
            'orbit_distance' => $orbitNumber + 1,
            'temperature' => $temperature,
            'color' => $planetType['color'],
        ]);
    }

    private function selectPlanetType(): array
    {
        $weights = array_column($this->planetTypes, 'rarity');
        $totalWeight = array_sum($weights);
        $randomValue = mt_rand() / mt_getrandmax() * $totalWeight;

        $cumulative = 0;
        foreach ($this->planetTypes as $type) {
            $cumulative += $type['rarity'];
            if ($randomValue <= $cumulative) {
                return $type;
            }
        }

        return $this->planetTypes[0];
    }

    private function generateSpecialFeatures(string $planetType): array
    {
        $features = [];

        foreach ($this->specialFeatures as $feature => $config) {
            if ($config['types'] === ['all'] || in_array($planetType, $config['types'])) {
                if ((mt_rand() / mt_getrandmax()) < $config['rarity']) {
                    $features[] = $feature;
                }
            }
        }

        return $features;
    }

    private function calculateRealisticTemperature(StarSystem $system, int $orbitNumber, array $planetType): float
    {
        $starConfig = $this->starTypes[$system->star_type];
        $baseStarTemp = ($starConfig['temp_range'][0] + $starConfig['temp_range'][1]) / 2;

        // Температура уменьшается с расстоянием (обратный квадрат)
        $distanceFactor = 1 / pow($orbitNumber + 1, 0.5);

        // Базовая температура планеты
        $baseTemp = $baseStarTemp * $distanceFactor * 0.0001; // Масштабируем до разумных значений

        // Добавляем вариацию в зависимости от типа планеты
        $typeVariation = mt_rand(-50, 50);

        // Газовая гиганты обычно холоднее
        if ($planetType['name'] === 'gas_giant' || $planetType['name'] === 'ice_giant') {
            $typeVariation -= 50;
        }

        // Вулканические планеты горячее
        if ($planetType['name'] === 'volcanic') {
            $typeVariation += 100;
        }

        $finalTemp = $baseTemp + $typeVariation;

        // Ограничиваем температурой в разумных пределах для типа планеты
        $finalTemp = max($planetType['temp_range'][0], min($planetType['temp_range'][1], $finalTemp));

        return round($finalTemp, 1);
    }

    private function createSeed(int $x, int $y, int $z): int
    {
        return crc32("{$x}-{$y}-{$z}");
    }
    private function checkForLife(array $planetType, int $orbitNumber, string $starType): bool
    {
        // Зона обитаемости зависит от типа звезды
        $habitableZone = $this->getHabitableZone($starType);

        // Проверяем что планета в зоне обитаемости
        $isInHabitableZone = ($orbitNumber + 1) >= $habitableZone[0] && ($orbitNumber + 1) <= $habitableZone[1];

        if (!$isInHabitableZone) {
            return false;
        }

        // Базовый шанс жизни для типа планеты
        $baseChance = $planetType['life_chance'];

        // Увеличиваем шанс для подходящих типов планет
        if (in_array($planetType['name'], ['jungle', 'oceanic', 'terrestrial', 'swamp'])) {
            $baseChance *= 5;
        }

        return (mt_rand() / mt_getrandmax()) < $baseChance;
    }
    private function getHabitableZone(string $starType): array
    {
        // Зона обитаемости в номерах орбит для разных типов звезд
        $zones = [
            'M' => [2, 4],   // Красные карлики - близкая зона
            'K' => [3, 5],   // Оранжевые карлики
            'G' => [3, 6],   // Желтые карлики (как Солнце)
            'F' => [4, 7],   // Желто-белые
            'A' => [5, 8],   // Белые звезды
            'B' => [6, 10],  // Голубые гиганты
        ];

        return $zones[$starType] ?? [3, 6];
    }

}

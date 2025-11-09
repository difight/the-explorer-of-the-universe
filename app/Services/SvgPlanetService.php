<?php

namespace App\Services;

class SvgPlanetService
{
    public function generateSvgPlanet(array $planetData): string
    {
        $type = $planetData['type'];
        $temperature = $planetData['temperature'];
        $orbitDistance = $planetData['orbit_distance'];
        $features = $planetData['special_features'];
        $hasLife = $planetData['has_life'];
        $size = max(80, min(180, $planetData['size'] / 200));

        $color = $this->getPlanetColor($type, $temperature, $orbitDistance);
        $rotationSpeed = $this->getRotationSpeed($type);
        $rotationDirection = $this->getRotationDirection();

        $svg = '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">';

        // Фон космоса
        $svg .= '<rect width="400" height="400" fill="#000000"/>';

        // Звезды на фоне (добавляем ДО планеты)
        $svg .= $this->generateStars();

        // Группа для планеты с SVG анимацией вращения
        $svg .= '<g>';

        // Анимация вращения с разной скоростью и направлением
        $svg .= '<animateTransform attributeName="transform" type="rotate" from="0 200 200" to="' . ($rotationDirection * 360) . ' 200 200" dur="' . $rotationSpeed . 's" repeatCount="indefinite"/>';

        // Белая основа для гарантии непрозрачности
        $svg .= '<circle cx="200" cy="200" r="' . min($size, 180) . '" fill="#FFFFFF"/>';

        // Основная планета
        $svg .= '<circle cx="200" cy="200" r="' . min($size, 180) . '" fill="' . $color . '"/>';

        // Текстуры планеты
        $svg .= $this->generatePlanetBody($type, $color, $size, $features, $hasLife, $temperature, $orbitDistance);

        $svg .= '</g>';

        // Кольца (если есть) - ВНУТРИ группы вращения чтобы они вращались вместе с планетой
        if (in_array('rings', $features)) {
            $svg .= '<g>';
            $svg .= '<animateTransform attributeName="transform" type="rotate" from="0 200 200" to="' . ($rotationDirection * 360) . ' 200 200" dur="' . $rotationSpeed . 's" repeatCount="indefinite"/>';
            $svg .= $this->generateRings(min($size, 180));
            $svg .= '</g>';
        }

        // Аврора (если есть) - ВНЕ группы вращения чтобы она не вращалась
        if (in_array('aurora_borealis', $features)) {
            $svg .= $this->addAurora(200, 200, min($size, 180));
        }

        $svg .= '</svg>';

        return $svg;
    }

    private function getRotationSpeed(string $planetType): int
    {
        $speeds = [
            'gas_giant' => 8,   // Быстрое вращение
            'ice_giant' => 12,  // Среднее вращение
            'terrestrial' => 20, // Медленное вращение
            'oceanic' => 25,
            'desert' => 22,
            'jungle' => 18,
            'tundra' => 30,
            'volcanic' => 15,
            'toxic' => 28,
            'crystal' => 35,
            'swamp' => 24,
            'barren' => 26,
        ];

        return $speeds[$planetType] ?? 20;
    }

    private function getRotationDirection(): string
    {
        // Случайное направление вращения
        return rand(0, 1) ? 1 : -1;
    }

    private function generateRings(float $planetSize): string
    {
        $centerX = 200;
        $centerY = 200;

        $ring1Rx = $planetSize * 1.8;
        $ring1Ry = $planetSize * 0.3;
        $ring2Rx = $planetSize * 1.5;
        $ring2Ry = $planetSize * 0.2;

        return
            '<ellipse cx="' . $centerX . '" cy="' . $centerY . '" rx="' . $ring1Rx . '" ry="' . $ring1Ry . '" fill="none" stroke="#B0C4DE" stroke-width="8" transform="rotate(45 200 200)"/>' .
            '<ellipse cx="' . $centerX . '" cy="' . $centerY . '" rx="' . $ring2Rx . '" ry="' . $ring2Ry . '" fill="none" stroke="#D3D3D3" stroke-width="6" transform="rotate(45 200 200)"/>';
    }

    private function generateStars(): string
    {
        $stars = '';
        for ($i = 0; $i < 30; $i++) {
            $x = rand(10, 390);
            $y = rand(10, 390);
            $size = rand(1, 3) / 2;
            $brightness = rand(7, 10) / 10;

            $stars .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $size . '" fill="#FFFFFF" opacity="' . $brightness . '">';
            $stars .= '<animate attributeName="opacity" values="' . ($brightness * 0.5) . ';' . $brightness . ';' . ($brightness * 0.5) . '" dur="' . rand(2, 5) . 's" repeatCount="indefinite"/>';
            $stars .= '</circle>';
        }
        return $stars;
    }

    private function generatePlanetBody(string $type, string $color, float $size, array $features, bool $hasLife, float $temperature, int $orbitDistance): string
    {
        $centerX = 200;
        $centerY = 200;
        $planetSize = min($size, 180);
        $svg = '';

        switch ($type) {
            case 'gas_giant':
                $svg .= $this->addGasGiantPattern($centerX, $centerY, $planetSize);
                break;
            case 'volcanic':
                $svg .= $this->addVolcanicPattern($centerX, $centerY, $planetSize);
                break;
            case 'jungle':
                $svg .= $this->addJunglePattern($centerX, $centerY, $planetSize, $hasLife);
                break;
            case 'oceanic':
                $svg .= $this->addOceanicPattern($centerX, $centerY, $planetSize);
                break;
            case 'desert':
                $svg .= $this->addDesertPattern($centerX, $centerY, $planetSize);
                break;
            case 'ice_giant':
            case 'tundra':
                $svg .= $this->addIcePattern($centerX, $centerY, $planetSize);
                break;
            case 'toxic':
                $svg .= $this->addToxicPattern($centerX, $centerY, $planetSize);
                break;
            case 'crystal':
                $svg .= $this->addCrystalPattern($centerX, $centerY, $planetSize);
                break;
            case 'swamp':
                $svg .= $this->addSwampPattern($centerX, $centerY, $planetSize, $hasLife);
                break;
            case 'barren':
                $svg .= $this->addBarrenPattern($centerX, $centerY, $planetSize);
                break;
            case 'terrestrial':
                $svg .= $this->addTerrestrialPattern($centerX, $centerY, $planetSize, $hasLife);
                break;
        }

        if (in_array('volcanic_activity', $features)) {
            $svg .= $this->addVolcanoes($centerX, $centerY, $planetSize);
        }

        return $svg;
    }

    private function getPlanetColor(string $type, float $temperature, int $orbitDistance): string
    {
        $baseColors = [
            'jungle' => '#2E8B57',
            'desert' => '#D2B48C',
            'oceanic' => '#1E90FF',
            'volcanic' => '#8B0000',
            'gas_giant' => '#FF8C00',
            'ice_giant' => '#87CEEB',
            'tundra' => '#F0F8FF',
            'barren' => '#696969',
            'toxic' => '#8A2BE2',
            'crystal' => '#FF69B4',
            'swamp' => '#556B2F',
            'terrestrial' => '#228B22'
        ];

        return $baseColors[$type] ?? '#696969';
    }

    private function addGasGiantPattern($cx, $cy, $size): string
    {
        $pattern = "";
        for ($i = 0; $i < 8; $i++) {
            $angle = $i * 45;
            $width = $size * 0.8;
            $height = $size * 0.1;
            $pattern .= '<ellipse cx="' . $cx . '" cy="' . $cy . '" rx="' . $width . '" ry="' . $height . '" fill="#FF4500" transform="rotate(' . $angle . ' ' . $cx . ' ' . $cy . ')"/>';
        }
        return $pattern;
    }

    private function addVolcanicPattern($cx, $cy, $size): string
    {
        $pattern = "";
        for ($i = 0; $i < 8; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.3, $size * 0.7);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $volcanoSize = rand($size * 0.05, $size * 0.08);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $volcanoSize . '" fill="#8B0000"/>';
        }
        return $pattern;
    }

    private function addJunglePattern($cx, $cy, $size, bool $hasLife): string
    {
        $pattern = "";
        for ($i = 0; $i < 4; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.2, $size * 0.5);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $continentSize = rand($size * 0.15, $size * 0.25);
            $pattern .= '<ellipse cx="' . $x . '" cy="' . $y . '" rx="' . $continentSize . '" ry="' . ($continentSize * 0.7) . '" fill="#228B22" transform="rotate(' . $angle . ' ' . $x . ' ' . $y . ')"/>';
        }
        return $pattern;
    }

    private function addOceanicPattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 6; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.3, $size * 0.6);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $islandSize = rand($size * 0.04, $size * 0.08);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $islandSize . '" fill="#32CD32"/>';
        }
        return $pattern;
    }

    private function addDesertPattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 10; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.2, $size * 0.7);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $duneWidth = rand($size * 0.08, $size * 0.15);
            $duneHeight = rand($size * 0.02, $size * 0.04);
            $pattern .= '<ellipse cx="' . $x . '" cy="' . $y . '" rx="' . $duneWidth . '" ry="' . $duneHeight . '" fill="#C19A6B" transform="rotate(' . $angle . ' ' . $x . ' ' . $y . ')"/>';
        }
        return $pattern;
    }

    private function addIcePattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 5; $i++) {
            $angle = $i * 72;
            $distance = rand($size * 0.1, $size * 0.3);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $iceSize = rand($size * 0.15, $size * 0.25);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $iceSize . '" fill="#E6E6FA"/>';
        }
        return $pattern;
    }

    private function addToxicPattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 6; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.2, $size * 0.6);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $cloudSize = rand($size * 0.08, $size * 0.15);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $cloudSize . '" fill="#8A2BE2"/>';
        }
        return $pattern;
    }

    private function addCrystalPattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 10; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.2, $size * 0.7);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $crystalSize = rand($size * 0.03, $size * 0.06);
            $pattern .= '<polygon points="' . $x . ',' . ($y - $crystalSize) . ' ' . ($x - $crystalSize) . ',' . ($y + $crystalSize) . ' ' . ($x + $crystalSize) . ',' . ($y + $crystalSize) . '" fill="#FF69B4"/>';
        }
        return $pattern;
    }

    private function addSwampPattern($cx, $cy, $size, bool $hasLife): string
    {
        $pattern = '';
        for ($i = 0; $i < 8; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.2, $size * 0.6);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $swampSize = rand($size * 0.06, $size * 0.12);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $swampSize . '" fill="#556B2F"/>';
        }
        return $pattern;
    }

    private function addBarrenPattern($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 15; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.1, $size * 0.8);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $craterSize = rand($size * 0.02, $size * 0.05);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $craterSize . '" fill="#808080"/>';
        }
        return $pattern;
    }

    private function addTerrestrialPattern($cx, $cy, $size, bool $hasLife): string
    {
        $pattern = '';
        for ($i = 0; $i < 3; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.1, $size * 0.4);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $continentSize = rand($size * 0.15, $size * 0.3);
            $pattern .= '<ellipse cx="' . $x . '" cy="' . $y . '" rx="' . $continentSize . '" ry="' . ($continentSize * 0.8) . '" fill="#32CD32" transform="rotate(' . $angle . ' ' . $x . ' ' . $y . ')"/>';
        }
        return $pattern;
    }

    private function addVolcanoes($cx, $cy, $size): string
    {
        $pattern = '';
        for ($i = 0; $i < 4; $i++) {
            $angle = rand(0, 360);
            $distance = rand($size * 0.4, $size * 0.7);
            $x = $cx + $distance * cos(deg2rad($angle));
            $y = $cy + $distance * sin(deg2rad($angle));
            $volcanoSize = rand($size * 0.06, $size * 0.12);
            $pattern .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $volcanoSize . '" fill="#8B0000"/>';
        }
        return $pattern;
    }

    private function addAurora($cx, $cy, $size): string
    {
        $pattern = '';
        $auroraSize = $size * 1.8;

        for ($i = 0; $i < 2; $i++) {
            $offset = $i * 20;
            $auroraHeight = $size * (0.15 - $i * 0.05);
            $colors = ['#00FF7F', '#7B68EE'];

            $pattern .= '<ellipse cx="' . $cx . '" cy="' . ($cy - $offset) . '" rx="' . $auroraSize . '" ry="' . $auroraHeight . '" fill="' . $colors[$i] . '" transform="rotate(20 ' . $cx . ' ' . $cy . ')">';
            $pattern .= '<animate attributeName="opacity" values="0.3;0.7;0.3" dur="4s" repeatCount="indefinite"/>';
            $pattern .= '</ellipse>';
        }
        return $pattern;
    }
}

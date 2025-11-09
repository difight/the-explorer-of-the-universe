<?php

namespace App\Services;

class TravelTimeService
{
    private array $travelTimes = [
        'M' => 6,    // Красный карлик - 6 часов
        'K' => 12,   // Оранжевый карлик - 12 часов
        'G' => 24,   // Желтый карлик - 24 часа
        'F' => 36,   // Желто-белый - 36 часов
        'A' => 48,   // Белая звезда - 48 часов
        'B' => 72,   // Голубой гигант - 72 часа
    ];

    public function calculateForStarType(string $starType): int
    {
        return $this->travelTimes[$starType] ?? 24; // По умолчанию 24 часа
    }

    public function getTravelTimes(): array
    {
        return $this->travelTimes;
    }

    public function updateTravelTime(string $starType, int $hours): void
    {
        $this->travelTimes[$starType] = $hours;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Str;

class NameModerationService
{
    private array $profanityWords = [
        'мат', 'хуй', 'пизда', 'ебал', 'блядь', 'гандон', 'мудила', 'шалава',
        'fuck', 'shit', 'asshole', 'bitch', 'dick', 'pussy', 'whore', 'cunt',
        // Добавьте больше запрещенных слов
    ];

    private array $advertisingWords = [
        'купить', 'продать', 'цена', 'скидка', 'акция', 'магазин', 'интернет',
        'buy', 'sell', 'price', 'discount', 'sale', 'shop', 'store', 'http://',
        'www.', '.com', '.ru', '.net'
    ];

    private array $reservedNames = [
        'земля', 'марс', 'венера', 'юпитер', 'сатурн', 'меркурий', 'нептун', 'уран',
        'плутон', 'earth', 'mars', 'venus', 'jupiter', 'saturn', 'mercury'
    ];

    public function validateName(string $name): array
    {
        $name = Str::lower(trim($name));
        $errors = [];

        // Проверка длины
        if (Str::length($name) < 2) {
            $errors[] = 'Имя слишком короткое (минимум 2 символа)';
        }

        if (Str::length($name) > 50) {
            $errors[] = 'Имя слишком длинное (максимум 50 символов)';
        }

        // Проверка на запрещенные слова
        if ($this->containsProfanity($name)) {
            $errors[] = 'Имя содержит нецензурные выражения';
        }

        // Проверка на рекламу
        if ($this->containsAdvertising($name)) {
            $errors[] = 'Имя содержит рекламный контент';
        }

        // Проверка на зарезервированные имена
        if ($this->isReservedName($name)) {
            $errors[] = 'Это имя зарезервировано системой';
        }

        // Проверка на повторяющиеся символы
        if ($this->hasRepeatingCharacters($name)) {
            $errors[] = 'Имя содержит слишком много повторяющихся символов';
        }

        // Проверка формата (только буквы, цифры, пробелы, дефисы, точки)
        if (!preg_match('/^[a-zA-Zа-яА-Я0-9\s\-\.]+$/u', $name)) {
            $errors[] = 'Имя содержит недопустимые символы';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function containsProfanity(string $text): bool
    {
        foreach ($this->profanityWords as $word) {
            if (Str::contains($text, $word)) {
                return true;
            }
        }
        return false;
    }

    private function containsAdvertising(string $text): bool
    {
        foreach ($this->advertisingWords as $word) {
            if (Str::contains($text, $word)) {
                return true;
            }
        }
        return false;
    }

    private function isReservedName(string $name): bool
    {
        return in_array($name, $this->reservedNames);
    }

    private function hasRepeatingCharacters(string $text): bool
    {
        // Проверяем на 4+ одинаковых символа подряд
        return preg_match('/(.)\1{3,}/', $text);
    }

    public function suggestAlternative(string $originalName): string
    {
        // Генерация альтернативного имени если нужно
        $suffixes = [' Prime', ' Major', ' Minor', ' Alpha', ' Beta', ' Gamma'];
        return $originalName . $suffixes[array_rand($suffixes)];
    }
}
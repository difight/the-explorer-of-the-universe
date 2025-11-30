<?php

use App\Services\NameModerationService;

beforeEach(function () {
    $this->nameModerationService = new NameModerationService();
});

it('can detect profanity in names', function () {
    $profaneNames = [
        'BadWord',
        'AnotherBadWord',
        'YetAnotherBadWord',
    ];

    foreach ($profaneNames as $name) {
        expect($this->nameModerationService->containsProfanity($name))->toBeTrue();
    }
});

it('allows clean names', function () {
    $cleanNames = [
        'Beautiful Planet',
        'Alpha Centauri Bb',
        'Kepler-442b',
        'New Terra',
        'Ocean World',
    ];

    foreach ($cleanNames as $name) {
        expect($this->nameModerationService->containsProfanity($name))->toBeFalse();
    }
});

it('is case insensitive for profanity detection', function () {
    $profaneNames = [
        'badword',
        'BADWORD',
        'BaDwOrD',
        'AnotherBadWord',
        'anotherbadword',
    ];

    foreach ($profaneNames as $name) {
        expect($this->nameModerationService->containsProfanity($name))->toBeTrue();
    }
});

it('handles empty and null names', function () {
    expect($this->nameModerationService->containsProfanity(''))->toBeFalse();
    expect($this->nameModerationService->containsProfanity(null))->toBeFalse();
});

it('handles special characters in names', function () {
    $cleanNamesWithSpecialChars = [
        'Planet-X',
        'New_World',
        'Earth-like',
        'Ocean.World',
    ];

    foreach ($cleanNamesWithSpecialChars as $name) {
        expect($this->nameModerationService->containsProfanity($name))->toBeFalse();
    }
});

it('handles numbers in names', function () {
    $cleanNamesWithNumbers = [
        'Planet 9',
        'Kepler 442b',
        '51 Pegasi b',
        'TRAPPIST-1e',
    ];

    foreach ($cleanNamesWithNumbers as $name) {
        expect($this->nameModerationService->containsProfanity($name))->toBeFalse();
    }
});

it('moderates names correctly', function () {
    $cleanName = 'Beautiful Planet';
    $moderatedCleanName = $this->nameModerationService->moderateName($cleanName);
    expect($moderatedCleanName)->toBe($cleanName);

    // Для неприемлемых имен сервис должен возвращать null или false
    // В зависимости от реализации, предположим, что он возвращает false
    $profaneName = 'BadWord';
    $moderatedProfaneName = $this->nameModerationService->moderateName($profaneName);
    expect($moderatedProfaneName)->toBeFalse();
});

it('handles unicode characters', function () {
    $unicodeNames = [
        'Planète Beau',
        'Schöne Welt',
        'Καλός Κόσμος',
        'Красивая Планета',
    ];

    foreach ($unicodeNames as $name) {
        // Предполагаем, что сервис пропускает имена на других языках
        // если они не содержат запрещенных слов на английском
        expect($this->nameModerationService->containsProfanity($name))->toBeFalse();
    }
});

it('handles long names', function () {
    $longCleanName = str_repeat('A', 100) . ' Planet';
    expect($this->nameModerationService->containsProfanity($longCleanName))->toBeFalse();

    $longProfaneName = 'BadWord ' . str_repeat('A', 100);
    expect($this->nameModerationService->containsProfanity($longProfaneName))->toBeTrue();
});
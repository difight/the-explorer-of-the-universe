<?php

use App\Http\Controllers\Api\SatelliteController;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlanetNamingController;
use App\Http\Controllers\Api\Admin\ModerationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Спутник
    Route::get('/satellite', [SatelliteController::class, 'show']);
    Route::post('/satellite/travel', [SatelliteController::class, 'travel']);
    Route::post('/satellite/repair', [SatelliteController::class, 'repair']);

    // Системы и планеты
    Route::get('/systems/current', [SystemController::class, 'current']);
    Route::get('/systems/{x}/{y}/{z}', [SystemController::class, 'show']);
    Route::get('/systems/{x}/{y}/{z}/planets', [SystemController::class, 'planets']);

    // Открытия
    Route::get('/discoveries', [DiscoveryController::class, 'index']);
    Route::post('/discoveries/{discovery}/name', [DiscoveryController::class, 'namePlanet']);

    // Достижения
    Route::get('/achievements', [AchievementController::class, 'index']);

    // Игрок
    Route::get('/user/stats', [SatelliteController::class, 'stats']);

    // Именование планет
    Route::prefix('planets')->group(function () {
        Route::post('/{planet}/name', [PlanetNamingController::class, 'namePlanet']);
        Route::get('/nameable', [PlanetNamingController::class, 'getUserNameablePlanets']);
        Route::get('/named', [PlanetNamingController::class, 'getUserNamedPlanets']);
    });
});

// Публичные маршруты (рейтинги, зал славы)
Route::get('/leaderboards/explorers', [AchievementController::class, 'leaderboard']);
Route::get('/hall-of-fame/life', [DiscoveryController::class, 'hallOfFame']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::prefix('moderation')->group(function () {
        Route::get('/pending-names', [ModerationController::class, 'getPendingNames']);
        Route::post('/discoveries/{discovery}/approve', [ModerationController::class, 'approveName']);
        Route::post('/discoveries/{discovery}/reject', [ModerationController::class, 'rejectName']);
        Route::get('/stats', [ModerationController::class, 'getModerationStats']);
    });
});

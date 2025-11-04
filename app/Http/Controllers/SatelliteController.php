<?php

namespace App\Http\Controllers;

use App\Models\Satellite;
use App\Models\StarSystem;
use App\Services\PlanetGenerator;
use Illuminate\Http\Request;

class SatelliteController extends Controller
{
    public function dashboard()
    {
        $satellite = auth()->user()->satellite;
        $currentSystem = $satellite->currentSystem;

        // Генерируем планеты если нужно
        (new PlanetGenerator())->generateForSystem($currentSystem);

        $currentSystem->load('planets.discoveries');

        return view('satellite.dashboard', compact('satellite', 'currentSystem'));
    }

    public function travel(Request $request)
    {
        $request->validate([
            'direction_x' => 'required|integer|between:-1,1',
            'direction_y' => 'required|integer|between:-1,1',
            'direction_z' => 'required|integer|between:-1,1',
        ]);

        $satellite = auth()->user()->satellite;

        if ($satellite->status === 'traveling') {
            return back()->with('error', 'Спутник уже в полете');
        }

        if ($satellite->fuel < 10) {
            return back()->with('error', 'Недостаточно топлива');
        }

        $targetX = $satellite->current_x + $request->direction_x;
        $targetY = $satellite->current_y + $request->direction_y;
        $targetZ = $satellite->current_z + $request->direction_z;

        // Находим или создаем целевую систему
        $targetSystem = StarSystem::findOrCreateAt($targetX, $targetY, $targetZ);

        $satellite->update([
            'target_x' => $targetX,
            'target_y' => $targetY,
            'target_z' => $targetZ,
            'arrival_time' => now()->addHours(24), // Полет занимает 24 часа
            'status' => 'traveling',
            'fuel' => $satellite->fuel - 10
        ]);

        return back()->with('success', 'Спутник отправлен в ' . $targetSystem->name);
    }

    public function checkArrival()
    {
        $satellite = auth()->user()->satellite;

        if ($satellite->status === 'traveling' && $satellite->arrival_time->isPast()) {
            // Спутник прибыл
            $satellite->update([
                'current_x' => $satellite->target_x,
                'current_y' => $satellite->target_y,
                'current_z' => $satellite->target_z,
                'target_x' => null,
                'target_y' => null,
                'target_z' => null,
                'arrival_time' => null,
                'status' => 'idle'
            ]);

            return back()->with('success', 'Спутник прибыл в новую систему!');
        }

        return back();
    }
}

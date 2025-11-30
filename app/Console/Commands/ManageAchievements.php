<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ManageAchievements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'achievements:manage {action : Action to perform (list, create, update, delete)} {--id= : Achievement definition ID for update/delete} {--name= : Achievement name for create/update} {--description= : Achievement description for create/update} {--icon= : Achievement icon for create/update} {--type= : Achievement type for create/update} {--threshold= : Achievement threshold for create/update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage achievement definitions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list':
                $this->listAchievements();
                break;
            case 'create':
                $this->createAchievement();
                break;
            case 'update':
                $this->updateAchievement();
                break;
            case 'delete':
                $this->deleteAchievement();
                break;
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
        
        return 0;
    }
    
    private function listAchievements(): void
    {
        $achievements = \App\Models\AchievementDefinition::all();
        
        if ($achievements->isEmpty()) {
            $this->info('No achievements found.');
            return;
        }
        
        $this->table(
            ['ID', 'Name', 'Type', 'Threshold', 'Active'],
            $achievements->map(function ($achievement) {
                return [
                    $achievement->id,
                    $achievement->name,
                    $achievement->type,
                    $achievement->threshold,
                    $achievement->is_active ? 'Yes' : 'No',
                ];
            })->toArray()
        );
    }
    
    private function createAchievement(): void
    {
        $name = $this->option('name') ?? $this->ask('Enter achievement name');
        $description = $this->option('description') ?? $this->ask('Enter achievement description');
        $icon = $this->option('icon') ?? $this->ask('Enter achievement icon');
        $type = $this->option('type') ?? $this->choice('Select achievement type', ['discoveries', 'named_planets', 'satellites_sent', 'energy_spent', 'planet_type', 'special']);
        $threshold = $this->option('threshold') ?? $this->ask('Enter achievement threshold');
        
        $achievement = \App\Models\AchievementDefinition::create([
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'type' => $type,
            'threshold' => $threshold,
            'is_active' => true,
        ]);
        
        $this->info("Achievement created with ID: {$achievement->id}");
    }
    
    private function updateAchievement(): void
    {
        $id = $this->option('id') ?? $this->ask('Enter achievement ID to update');
        
        $achievement = \App\Models\AchievementDefinition::find($id);
        
        if (!$achievement) {
            $this->error("Achievement with ID {$id} not found.");
            return;
        }
        
        $name = $this->option('name') ?? $this->ask('Enter achievement name (leave blank to keep current)', $achievement->name);
        $description = $this->option('description') ?? $this->ask('Enter achievement description (leave blank to keep current)', $achievement->description);
        $icon = $this->option('icon') ?? $this->ask('Enter achievement icon (leave blank to keep current)', $achievement->icon);
        $type = $this->option('type') ?? $this->choice('Select achievement type', ['discoveries', 'named_planets', 'satellites_sent', 'energy_spent', 'planet_type', 'special'], $achievement->type);
        $threshold = $this->option('threshold') ?? $this->ask('Enter achievement threshold (leave blank to keep current)', $achievement->threshold);
        
        $achievement->update(array_filter([
            'name' => $name !== '' ? $name : null,
            'description' => $description !== '' ? $description : null,
            'icon' => $icon !== '' ? $icon : null,
            'type' => $type,
            'threshold' => $threshold !== '' ? $threshold : null,
        ]));
        
        $this->info("Achievement with ID {$id} updated.");
    }
    
    private function deleteAchievement(): void
    {
        $id = $this->option('id') ?? $this->ask('Enter achievement ID to delete');
        
        $achievement = \App\Models\AchievementDefinition::find($id);
        
        if (!$achievement) {
            $this->error("Achievement with ID {$id} not found.");
            return;
        }
        
        if ($this->confirm("Are you sure you want to delete achievement '{$achievement->name}'?")) {
            $achievement->delete();
            $this->info("Achievement with ID {$id} deleted.");
        }
    }
}

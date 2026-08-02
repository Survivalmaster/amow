<?php

namespace Database\Seeders;

use App\Models\GameJob;
use Illuminate\Database\Seeder;

class GameJobSeeder extends Seeder
{
    public function run(): void
    {
        GameJob::query()->updateOrCreate(
            ['slug' => 'begger'],
            [
                'name' => 'Begger',
                'description' => 'A rough start, but it keeps a little money coming in while you build your name.',
                'min_pay' => 10,
                'max_pay' => 30,
                'required_level' => 0,
                'work_cooldown_minutes' => 5,
                'stamina_decrease' => 10,
                'experience_reward' => 5,
                'working_display_message' => 'Is begging in the city.',
                'is_starter' => true,
                'is_active' => true,
            ]
        );
    }
}

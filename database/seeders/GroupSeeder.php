<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        /** 2 родительские без детей */
        Group::factory()->count(2)->create();

        /** 5 родительских с одним наследником */
        Group::factory()->count(5)->create()->each(function ($group) {
            Group::factory()->create([
                'parent_id' => $group->id
            ]);
        });

        /** 5 родительских с двумя наследниками */
        Group::factory()->count(5)->create()->each(function ($group) {
            Group::factory(2)->create([
                'parent_id' => $group->id
            ]);
        });
    }
}

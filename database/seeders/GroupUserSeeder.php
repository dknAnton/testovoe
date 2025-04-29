<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Определяем самые глубокие группы.
 * Привязываем пользователей к самым глубоким группам.
 */
class GroupUserSeeder extends Seeder
{
    public function run(): void
    {
        $deepestGroups = Group::doesntHave('children')->get();

        foreach ($deepestGroups as $group) {
            $randomUsers = User::inRandomOrder()->limit(rand(2,5))->pluck('id');
            $group->users()->attach($randomUsers);
        }
    }
}

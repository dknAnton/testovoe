<?php

namespace App\Services;

use App\DTO\GroupUsersTreeDTO;
use App\Models\Group;
use Illuminate\Support\Collection;

class GroupUserService
{
    /**
     * Формируем дерево групп с количеством уникальных пользователей
     *
     * @return array
     */
    public function getGroupUserTree(): array
    {
        $groups = Group::with('children')->get();

        // Определяем ID групп, которые являются листьями
        $leafGroupIds = $groups
            ->filter(fn($g) => $g->children->isEmpty())
            ->pluck('id');

        // Загружаем пользователей только для листовых групп (чтобы не грузить БД)
        $leafGroupsWithUsers = Group::with('users')
            ->whereIn('id', $leafGroupIds)
            ->get()
            ->keyBy('id');

        $groupMap = $groups->keyBy('id');

        // Подготавливаем данные в каждой группе:
        foreach ($groups as $group) {
            $group->children_ids = $group->children->pluck('id')->all();
            $group->user_ids = collect();

            // Если группа является листом, берем ее пользователей
            if (isset($leafGroupsWithUsers[$group->id])) {
                $group->user_ids = $leafGroupsWithUsers[$group->id]->users->pluck('id')->unique();
            }
        }

        // Сортируем группы снизу вверх для корректного подсчета пользователей в родительских гр.
        $sortedGroups = $this->sortGroupsBottomUp($groups);

        // Агрегируем пользователей от детей к родителям
        foreach ($sortedGroups as $group) {
            // Для каждой дочерней группы добавляем ее пользователей к родителю
            foreach ($group->children_ids ?? [] as $childId) {
                $group->user_ids = $group->user_ids->merge($groupMap[$childId]->user_ids);
            }

            /** Удаляем дубликаты пользователей в рамках одной группы */
            $group->user_ids = $group->user_ids?->unique();
        }

        // Строим дерево DTO, начиная только с корневых групп
        return $groups
            ->filter(fn($g) => !$g->parent_id)
            ->map(fn($g) => $this->buildDTO($g, $groupMap))
            ->map(fn(GroupUsersTreeDTO $dto) => $dto->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Рекурсивно строит DTO для группы и ее потомков
     *
     * @param Group $group Текущая группа
     * @param Collection $groupMap Карта всех групп
     * @return GroupUsersTreeDTO
     */
    private function buildDTO($group, $groupMap): GroupUsersTreeDTO
    {
        return new GroupUsersTreeDTO(
            group: $group->title,
            user_count: $group->user_ids->count(),
            // Рекурсивно строим DTO для дочерних групп
            children: collect($group->children_ids)
                ->map(fn($childId) => $this->buildDTO($groupMap[$childId], $groupMap))
                ->toArray()
        );
    }

    /**
     * Сортирует группы снизу вверх (от листьев к корням)
     *
     * @param Collection $groups Все группы
     * @return Collection Отсортированные группы
     */
    private function sortGroupsBottomUp(Collection $groups): Collection
    {
        $sorted = collect();
        $visited = [];

        // Рекурсивная функция для обхода дерева
        $visit = function ($group) use (&$visit, &$sorted, &$visited, $groups) {
            // Скипаем уже посещенные группы
            if (isset($visited[$group->id])) {
                return;
            }

            $visited[$group->id] = true;

            // Сначала посещаем всех детей
            foreach ($group->children as $child) {
                $visit($child);
            }

            // Затем добавляем текущую группу
            $sorted->push($group);
        };

        // Запускаем обход для всех групп
        foreach ($groups as $group) {
            $visit($group);
        }

        return $sorted;
    }
}

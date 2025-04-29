<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class GroupUserController extends Controller
{
    public function index(): JsonResponse
    {
        $groups = Group::with(['users', 'children.users'])->get();
        $groupMap = $groups->keyBy('id');

        $result = [];

        foreach ($groups as $group) {
            if (!$group->parent_id) {
                $data = $this->countUsersRecursive($group, $groupMap);
                $result[] = $this->cleanUp($data);
            }
        }

        return response()->json($result);
    }

    private function countUsersRecursive(Group $group, Collection $groupMap): array
    {
        if ($group->children->isEmpty()) {
            $userIds = $group->users->pluck('id')->unique();

            return [
                'group' => $group->title,
                'user_count' => $userIds->count(),
                'user_ids' => $userIds,
                'children' => [],
            ];
        }

        $children = [];
        $allUserIds = collect();

        foreach ($group->children as $child) {
            $childData = $this->countUsersRecursive($groupMap[$child->id], $groupMap);
            $children[] = $childData;
            $allUserIds = $allUserIds->merge($childData['user_ids']);
        }

        $allUserIds = $allUserIds->unique();

        return [
            'group' => $group->title,
            'user_count' => $allUserIds->count(),
            'user_ids' => $allUserIds,
            'children' => $children,
        ];
    }

    private function cleanUp(array $groupData): array
    {
        unset($groupData['user_ids']);

        if (!empty($groupData['children'])) {
            $groupData['children'] = array_map([$this, 'cleanUp'], $groupData['children']);
        }

        return $groupData;
    }
}

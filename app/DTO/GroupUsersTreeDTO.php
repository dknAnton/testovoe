<?php

namespace App\DTO;

class GroupUsersTreeDTO
{
    public function __construct(
        public string $group,
        public int $user_count,
        /** @var GroupUsersTreeDTO[] */
        public array $children = []
    ) {}

    public function toArray(): array
    {
        return [
            'group' => $this->group,
            'user_count' => $this->user_count,
            'children' => array_map(fn($child) => $child->toArray(), $this->children),
        ];
    }
}


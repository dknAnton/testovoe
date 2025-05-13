<?php

namespace App\Http\Controllers;

use App\Services\GroupUserService;
use Illuminate\Http\JsonResponse;

class GroupUserController extends Controller
{
    public function index(GroupUserService $service): JsonResponse
    {
        $tree = $service->getGroupUserTree();

        return response()->json($tree);
    }
}

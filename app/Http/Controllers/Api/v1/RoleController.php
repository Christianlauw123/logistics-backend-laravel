<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->roleService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    // public function store(StoreUserRequest $request): UserResource
    // {
    //     return new UserResource(
    //         $this->userService->create($request->validated())
    //     );
    // }

    // public function show(string $userId): UserResource
    // {
    //     return new UserResource(
    //         $this->userService->findOrFail($userId)
    //     );
    // }

    // public function update(UpdateUserRequest $request, string $userId): UserResource
    // {
    //     return new UserResource(
    //         $this->userService->update($userId, $request->validated())
    //     );
    // }

    // public function destroy(Request $request, string $userId): JsonResponse
    // {
    //     $this->userService->delete($userId, $request->user()?->id);
    //     return response()->json(['message' => 'Deleted.']);
    // }
}

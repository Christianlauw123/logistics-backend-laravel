<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->userService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        return new UserResource(
            $this->userService->create($request->validated())
        );
    }

    public function show(ShowUserRequest $id): UserResource
    {
        return new UserResource(
            $this->userService->findOrFail($id->user)
        );
    }

    public function update(UpdateUserRequest $request, ShowUserRequest $id): UserResource
    {
        return new UserResource(
            $this->userService->update($id->user, $request->validated())
        );
    }

    public function destroy(Request $request, ShowUserRequest $id): JsonResponse
    {
        $this->userService->delete($id->user, $request->user()?->id);
        return response()->json(['message' => 'Deleted.']);
    }
}

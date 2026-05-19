<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function create(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function update(string $id, array $data): User
    {
        $user = $this->userRepository->findOrFail($id);
        return $this->userRepository->update($user, $data);
    }

    public function delete(string $id, string $requestingUserId): void
    {
        // Business rule: cannot delete yourself
        if ($id === $requestingUserId) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user = $this->userRepository->findOrFail($id);
        $this->userRepository->delete($user);
    }
}

<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;

class UserService{
    // Inject the Repository automatically
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function registerUser(array $data): User
    {
        // 1. Default Role - Operator
        $defaultRoleId = '';
        // 2. Delegate database mutation to the Repository
        $user = $this->userRepository->createUserWithRole($data, $defaultRoleId);

        return $user;
    }
}
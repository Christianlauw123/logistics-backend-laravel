<?php

namespace App\Providers;

use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // When something asks for TransactionRepositoryInterface,
        // Laravel gives it a TransactionRepository instance.
        // $this->app->bind(
        //     TransactionRepository::class,
        // );
    }
}

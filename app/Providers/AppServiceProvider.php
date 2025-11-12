<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\IUser;
use App\Repositories\UserRepository;
use App\Interfaces\ICompte;
use App\Repositories\CompteRepository;
use App\Interfaces\ITransaction;
use App\Repositories\TransactionRepository;
use App\Services\CompteService;
use App\Services\TransactionService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Repository bindings
        $this->app->bind(IUser::class, UserRepository::class);
        $this->app->bind(ICompte::class, CompteRepository::class);
        $this->app->bind(ITransaction::class, TransactionRepository::class);

        // Service bindings
        $this->app->singleton(CompteService::class, function($app) {
            return new CompteService($app->make(ICompte::class));
        });

        $this->app->bind(TransactionService::class, function($app) {
            return new TransactionService(
                $app->make(ITransaction::class),
                $app->make(ICompte::class),
                $app->make(CompteService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}

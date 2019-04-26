<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Contracts\PermissionRegistrarInterface;
use Balfour\LaravelCustodian\Contracts\PermissionResolverInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param Custodian $custodian
     */
    public function boot(Custodian $custodian)
    {
        $this->publishes([__DIR__ . '/config.php' => config_path('custodian.php')], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        foreach (config('custodian.admins', []) as $email) {
            $custodian->addSuperAdmin($email);
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'custodian');

        $this->app->singleton(Custodian::class, function () {
            $registrar = app(PermissionRegistrarInterface::class);
            $resolver = app(PermissionResolverInterface::class);
            $gate = app(Gate::class);

            return new Custodian($registrar, $resolver, $gate);
        });

        $this->app->bind(PermissionRegistrarInterface::class, config('custodian.registrar'));
        $this->app->bind(PermissionResolverInterface::class, config('custodian.resolver'));
    }
}

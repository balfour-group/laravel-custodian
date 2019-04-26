<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Your application user model.
    |
    | The library will try to detect the path of your user model from the auth
    | config's default guard.
    |
    | If you are using a non default guard, you can specify it here:
    | eg: \App\User::class
    |
    */

    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Admins
    |--------------------------------------------------------------------------
    |
    | The email addresses here will be given super admin access on the custodian.
    |
    | These users will automatically pass all gate (permission) checks.
    |
    */

    'admins' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Registrar
    |--------------------------------------------------------------------------
    |
    | The registrar is used to register & retrieve all available application
    | permissions.
    |
    */

    'registrar' => \Balfour\LaravelCustodian\PermissionRegistrar::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Resolver
    |--------------------------------------------------------------------------
    |
    | The resolver is used to retrieve permissions for a user.
    |
    */

    'resolver' => \Balfour\LaravelCustodian\CachedPermissionResolver::class,

];

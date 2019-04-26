<?php

namespace Balfour\LaravelCustodian;

abstract class UserModelResolver
{
    /**
     * @var string
     */
    protected static $model;

    /**
     * @param string $model
     */
    public static function setModel($model)
    {
        static::$model = $model;
    }

    /**
     * @return string
     */
    public static function resolve()
    {
        return static::$model ?? static::resolveFromConfig();
    }

    /**
     * @return string|null
     */
    protected static function resolveFromConfig()
    {
        $model = config('custodian.user_model');

        if ($model) {
            return $model;
        }

        // if nothing in our config, we'll try resolve from the default auth guard

        $guard = config('auth.defaults.guard');

        if ($guard === null) {
            return null;
        }

        $provider = config(sprintf('auth.guards.%s.provider', $guard));

        if ($provider === null) {
            return null;
        }

        return config(sprintf('auth.providers.%s.model', $provider));
    }
}

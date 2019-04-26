<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Contracts\PermissionRegistrarInterface;
use Illuminate\Support\Str;

class PermissionRegistrar implements PermissionRegistrarInterface
{
    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var array
     */
    protected $permissions = [];

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * @param string|array $permission
     * @return $this
     */
    public function register($permission)
    {
        $permissions = is_array($permission) ? $permission : [$permission];

        foreach ($permissions as $permission) {
            if (!$this->isPermission($permission)) {
                $this->permissions[] = $permission;

                // trigger callbacks
                foreach ($this->callbacks as $callback) {
                    call_user_func($callback, $permission);
                }
            }
        }

        return $this;
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function isPermission($permission)
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return array
     */
    public function getPermissionsList()
    {
        $permissions = $this->permissions;
        sort($permissions);
        $permissions = array_combine($permissions, $permissions);
        return array_map(function ($permission) {
            return Str::title(str_replace('-', ' ', $permission));
        }, $permissions);
    }

    /**
     * @param callable $callback
     */
    public function onPermissionRegistered(callable $callback)
    {
        $this->callbacks[] = $callback;
    }
}

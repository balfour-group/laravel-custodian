<?php

namespace Balfour\LaravelCustodian\Contracts;

interface PermissionRegistrarInterface
{
    /**
     * @param string|array $permission
     * @return $this
     */
    public function register($permission);

    /**
     * @param string $permission
     * @return bool
     */
    public function isPermission($permission);

    /**
     * @return array
     */
    public function getPermissions();

    /**
     * @return array
     */
    public function getPermissionsList();

    /**
     * @param callable $callback
     */
    public function onPermissionRegistered(callable $callback);
}

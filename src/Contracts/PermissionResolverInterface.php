<?php

namespace Balfour\LaravelCustodian\Contracts;

interface PermissionResolverInterface
{
    /**
     * @param mixed $user
     * @return array
     */
    public function getPermissions($user);
}

<?php

namespace Balfour\LaravelCustodian\Events;

use Balfour\LaravelCustodian\Contracts\PermissionRegistrarInterface;

class PermissionRegistered
{
    /**
     * @var string
     */
    public $permission;

    /**
     * @var PermissionRegistrarInterface
     */
    public $registrar;

    /**
     * @param string $permission
     * @param PermissionRegistrarInterface $registrar
     */
    public function __construct($permission, PermissionRegistrarInterface $registrar)
    {
        $this->permission = $permission;
        $this->registrar = $registrar;
    }
}

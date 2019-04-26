<?php

namespace Balfour\LaravelCustodian\Events;

use Balfour\LaravelCustodian\Models\Role;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    /**
     * @var Role
     */
    public $role;

    /**
     * @param mixed $user
     * @param Role $role
     */
    public function __construct($user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}

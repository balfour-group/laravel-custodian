<?php

namespace Balfour\LaravelCustodian\Events;

use Balfour\LaravelCustodian\Models\Role;
use Illuminate\Queue\SerializesModels;

class RoleCreated
{
    use SerializesModels;

    /**
     * @var Role
     */
    public $role;

    /**
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}

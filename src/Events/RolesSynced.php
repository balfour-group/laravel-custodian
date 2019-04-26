<?php

namespace Balfour\LaravelCustodian\Events;

use Illuminate\Queue\SerializesModels;

class RolesSynced
{
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    /**
     * @var array
     */
    public $changes;

    /**
     * @param mixed $user
     * @param array $changes
     */
    public function __construct($user, array $changes)
    {
        $this->user = $user;
        $this->changes = $changes;
    }
}

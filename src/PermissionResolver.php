<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Contracts\PermissionResolverInterface;
use Balfour\LaravelCustodian\Events\RoleAssigned;
use Balfour\LaravelCustodian\Events\RoleCreated;
use Balfour\LaravelCustodian\Events\RoleDeleted;
use Balfour\LaravelCustodian\Events\RoleRevoked;
use Balfour\LaravelCustodian\Events\RolesSynced;
use Balfour\LaravelCustodian\Events\RoleUpdated;
use Balfour\LaravelCustodian\Models\Role;
use Illuminate\Contracts\Events\Dispatcher as Dispatcher;
use Illuminate\Database\Eloquent\Builder;

class PermissionResolver implements PermissionResolverInterface
{
    /**
     * @var array
     */
    protected $mappings = null;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        $events->listen(
            [
                RoleCreated::class,
                RoleUpdated::class,
                RoleDeleted::class,
                RoleAssigned::class,
                RoleRevoked::class,
                RolesSynced::class,
            ],
            function () {
                $this->mappings = null;
            }
        );
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function getPermissions($user)
    {
        if ($this->mappings === null) {
            $this->mappings = $this->build();
        }

        return $this->mappings[$user->id] ?? [];
    }

    /**
     * @return array
     */
    public function build()
    {
        $mappings = [];

        $model = UserModelResolver::resolve();
        $query = $model::query(); /** @var Builder $query */

        $users = $query->select('id')
            ->with('roles')
            ->get();

        foreach ($users as $user) {
            $mappings[$user->id] = [];

            foreach ($user->roles as $role) {
                /** @var Role $role */
                $permissions = $role->permissions ?? [];
                $mappings[$user->id] = array_merge($mappings[$user->id], $permissions);
            }

            $mappings[$user->id] = array_unique($mappings[$user->id]);
            $mappings[$user->id] = array_values($mappings[$user->id]);
        }

        return $mappings;
    }
}

<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Events\RoleAssigned;
use Balfour\LaravelCustodian\Events\RoleRevoked;
use Balfour\LaravelCustodian\Events\RolesSynced;
use Balfour\LaravelCustodian\Models\Role;

trait HasRoles
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @param mixed $role
     */
    public function assignRole($role)
    {
        $role = $this->resolveRoleModel($role);

        $this->roles()->attach($role);

        event(new RoleAssigned($this, $role));
    }

    /**
     * @param mixed $role
     */
    public function revokeRole($role)
    {
        $role = $this->resolveRoleModel($role);

        $this->roles()->detach($role);

        event(new RoleRevoked($this, $role));
    }

    /**
     * @param array $ids
     */
    public function syncRoles(array $ids)
    {
        $changes = $this->roles()->sync($ids);

        if (count($changes['attached']) > 0 || count($changes['detached']) > 0) {
            event(new RolesSynced($this, $changes));
        }
    }

    /**
     * @param mixed $role
     * @return Role
     */
    protected function resolveRoleModel($role)
    {
        if ($role instanceof Role) {
            return $role;
        } elseif (is_string($role)) {
            return Role::where('name', $role)
                ->firstOrFail();
        } else {
            return Role::findOrFail($role);
        }
    }
}

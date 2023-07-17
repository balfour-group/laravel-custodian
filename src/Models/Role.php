<?php

namespace Balfour\LaravelCustodian\Models;

use Balfour\LaravelCustodian\Events\RoleCreated;
use Balfour\LaravelCustodian\Events\RoleDeleted;
use Balfour\LaravelCustodian\Events\RoleUpdated;
use Balfour\LaravelCustodian\UserModelResolver;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => RoleCreated::class,
        'updated' => RoleUpdated::class,
        'deleted' => RoleDeleted::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(UserModelResolver::resolve());
    }

    /**
     * @param string $permission
     */
    public function give($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions[] = $permission;
        $permissions = array_unique($permissions);
        $permissions = array_values($permissions);
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * @param string $permission
     */
    public function revoke($permission)
    {
        $permissions = $this->permissions ?? [];
        $i = array_search($permission, $permissions);
        if ($i !== false) {
            unset($permissions[$i]);
        }
        $permissions = array_values($permissions);
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * @return array
     */
    public static function listify()
    {
        return static::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}

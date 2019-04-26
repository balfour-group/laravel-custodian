<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Contracts\PermissionRegistrarInterface;
use Balfour\LaravelCustodian\Contracts\PermissionResolverInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Model;

class Custodian
{
    /**
     * @var PermissionRegistrarInterface
     */
    protected $registrar;

    /**
     * @var PermissionResolverInterface
     */
    protected $resolver;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var array
     */
    protected $admins = [];

    /**
     * @param PermissionRegistrarInterface $registrar
     * @param PermissionResolverInterface $resolver
     * @param Gate $gate
     */
    public function __construct(
        PermissionRegistrarInterface $registrar,
        PermissionResolverInterface $resolver,
        Gate $gate
    ) {
        $this->registrar = $registrar;
        $this->resolver = $resolver;
        $this->gate = $gate;

        // when a permission is registered on the registrar, bind an auth check on the gate
        $this->registrar->onPermissionRegistered(function ($permission) {
            $this->gate->define($permission, function ($user) use ($permission) {
                return $this->can($user, $permission);
            });
        });
    }

    /**
     * @return PermissionRegistrarInterface
     */
    public function getRegistrar()
    {
        return $this->registrar;
    }

    /**
     * @return PermissionResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @param string|array $permission
     * @return $this
     */
    public function register($permission)
    {
        $this->registrar->register($permission);

        return $this;
    }

    /**
     * @param string|Model $email
     * @return $this
     */
    public function addSuperAdmin($email)
    {
        if ($email instanceof Model) {
            $email = $email->email;
        }

        if (!in_array($email, $this->admins)) {
            $this->admins[] = $email;
        }

        return $this;
    }

    /**
     * @param string|Model $email
     * @return bool
     */
    public function isSuperAdmin($email)
    {
        if ($email instanceof Model) {
            $email = $email->email;
        }

        return in_array($email, $this->admins);
    }

    /**
     * @return array
     */
    public function getAvailablePermissions()
    {
        return $this->registrar->getPermissions();
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function getUserPermissions($user)
    {
        $permissions = $this->resolver->getPermissions($user);

        // we filter out any permissions from the model which no longer exist
        $permissions = array_intersect($permissions, $this->registrar->getPermissions());

        return array_values($permissions);
    }

    /**
     * @param mixed $user
     * @param string $permission
     * @return bool
     */
    public function can($user, $permission)
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $permissions = $this->getUserPermissions($user);
        return in_array($permission, $permissions);
    }
}

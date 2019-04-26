<?php

namespace Balfour\LaravelCustodian;

use Balfour\LaravelCustodian\Contracts\PermissionResolverInterface;
use Balfour\LaravelCustodian\Events\RoleAssigned;
use Balfour\LaravelCustodian\Events\RoleCreated;
use Balfour\LaravelCustodian\Events\RoleDeleted;
use Balfour\LaravelCustodian\Events\RoleRevoked;
use Balfour\LaravelCustodian\Events\RolesSynced;
use Balfour\LaravelCustodian\Events\RoleUpdated;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Events\Dispatcher as Dispatcher;

class CachedPermissionResolver implements PermissionResolverInterface
{
    /**
     * @var PermissionResolver
     */
    protected $resolver;

    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var array
     */
    protected $mappings = null;

    /**
     * @param PermissionResolver $resolver
     * @param CacheRepository $cache
     * @param Dispatcher $events
     */
    public function __construct(PermissionResolver $resolver, CacheRepository $cache, Dispatcher $events)
    {
        $this->resolver = $resolver;
        $this->cache = $cache;

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
                $this->cache->forget('custodian.permission_mappings');
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
            $this->mappings = $this->cache->remember(
                'custodian.permission_mappings',
                Carbon::now()->addMinutes(30),
                function () {
                    return $this->resolver->build();
                }
            );
        }

        return $this->mappings[$user->id] ?? [];
    }
}

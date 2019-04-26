# laravel-custodian

A super lightweight library for handling role based permissions in Laravel.

*This library is in early release and is pending unit tests.*

## Table of Contents

* [Why](#why)
* [Installation](#installation)
* [Configuration](#configuration)
* [Configuring User Model](#configuring-user-model)
* [Usage](#usage)
    * [Creating Roles](#creating-roles)
    * [Registering Permissions](#registering-permissions)
    * [Listing Available Permissions](#listing-available-permissions)
    * [Assigning Permissions to Roles](#assigning-permissions-to-roles)
    * [Revoking Permissions from Roles](#revoking-permissions-from-roles)
    * [Assigning Roles to Users](#assigning-roles-to-users)
    * [Syncing User Roles](#syncing-user-roles)
    * [Revoking Roles from Users](#revoking-roles-from-users)
    * [Listing User Roles](#listing-user-roles)
    * [Retrieving a User's Permissions](#retrieving-a-users-permissions)
    * [Authorizing Users](#authorizing-users)

## Why?

There are some great existing role based authorization libraries out there, so why another one?

For our internal systems, we wanted something which is:

1. simple to use
1. does not contain all the bells and whistles
1. works with the framework's existing authorization library
1. focuses on performance first
1. elegantly modeled without a plethora of pivot tables - json type works fine!

We've managed to keep things simple and efficient by:

1. creating just 2 tables, a `roles` table and a `role_user` table mapping users to roles
1. storing assigned permissions in a json column on the `roles` table
1. performing just a single database query to retrieve all users, assigned roles & permissions
1. keeping an in-object mapping cache of user -> permissions
1. decorating this mapping cache with an external cache of your choice (memcached, redis, etc)

## Installation

```bash
composer require balfour/laravel-custodian
```

## Configuration

The package works out the box without any configuration, however if you'd like to publish
the config file, you can do so using:

`php artisan vendor:publish --provider="Balfour\LaravelCustodian\ServiceProvider"`

**Custom User Model**

The library will try to detect the path of your user model from the auth config's default guard.

If you'd like to use a custom model such as `\App\User::class`, you can specify a class path
in the `user_model` config value.

An alternative way to set the user model on the fly is via the
`Balfour\LaravelCustodian\UserModelResolver::setModel(\App\User::class)` function.

**Super Admins**

The `admins` config value takes an array of email addresses which should be given super admin
access on the custodian.  This means that these users will automatically pass all gate (permission)
checks.

We **do not** recommend using this outside of testing.  You should rather make use of Laravel's
`Gate::before` or `Gate::after` hooks to accomplish this.

## Configuring User Model

Add the `HasRoles` trait to your user model.

```php
namespace App;

use Balfour\LaravelCustodian\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;
}
```

This will establish a relationship between users and roles; and also give you the
following utility methods:

```php
/**
 * @param mixed $role
 */
public function assignRole($role)

/**
 * @param mixed $role
 */
public function revokeRole($role)

/**
 * @param array $ids
 */
public function syncRoles(array $ids)
```

## Usage

### Creating Roles

```php
use Balfour\LaravelCustodian\Models\Role;

$admin = Role::create(['name' => 'admin']);

$support = Role::create(['name' => 'customer-support']);
```

### Registering Permissions

You must register permissions before they can be assigned to roles.

The package does not store available permissions in the database, but instead, registers
them against a `PermissionRegistrar`.  This means that permissions must be registered
upon bootstrap, such as in your app's `AppServiceProvider`, or a module/package's
`ServiceProvider`.

```php
use Balfour\LaravelCustodian\Custodian;

$custodian = app(Custodian::class);
$custodian->register('create-user');
$custodian->register('view-user');
$custodian->register('view-user-support-tickets');
```

### Listing Available Permissions

```php
use Balfour\LaravelCustodian\Custodian;

$custodian = app(Custodian::class);
$permissions = $custodian->getAvailablePermissions();

// you could use this to generate a permission matrix for a user, or display a checkbox list
// of permissions on an edit user type of page
```

### Assigning Permissions to Roles

```php
use Balfour\LaravelCustodian\Models\Role;

$admin = Role::where('name', 'admin')->first();
$admin->give('create-user');
$admin->give('view-user');
```

### Revoking Permissions from Roles

```php
use Balfour\LaravelCustodian\Models\Role;

$admin = Role::where('name', 'admin')->first();
$admin->revoke('create-user');
```

### Assigning Roles to Users

```php
use App\User;
use Balfour\LaravelCustodian\Models\Role;

$user = User::find(1);
$user->assignRole('admin');

// you can also assign using a model
$admin = Role::where('name', 'admin')->first();
$user->assignRole($admin);

// or by id
$user->assignRole(1);
```

### Syncing User Roles

```php
use App\User;

$user = User::find(1);
$user->syncRoles([
    1,
    2,
]);
```

### Revoking Roles from Users

```php
use App\User;
use Balfour\LaravelCustodian\Models\Role;

$user = User::find(1);
$user->revokeRole('admin');

// you can also revoke using a model
$admin = Role::where('name', 'admin')->first();
$user->revokeRole($admin);

// or by id
$user->revokeRole(1);
```

### Listing User Roles

```php
use App\User;

$user = User::find(1);
$roles = $user->roles;
```

### Retrieving a User's Permissions

```php
use App\User;
use Balfour\LaravelCustodian\Custodian;

$custodian = app(Custodian::class);
$user = User::find(1);
$permissions = $custodian->getUserPermissions();
```

### Authorizing Users

The library does not add any special functionality for checking if a user can perform a specific
permission (or ability).

Please see the [Laravel Authorization documentation](https://laravel.com/docs/5.8/authorization#authorizing-actions-using-policies)
for more info on this subject.

```php
use App\User;

$user = User::find(1);
var_dump($user->can('create-user'));

// via middleware
Route::put('/users/{user}', function (User $user) {

})->middleware('can:view-user');

// via blade
@can('view-user)
    // ....
@endcan
```

## Arbiter

[![Build Status](https://travis-ci.org/dadamssg/arbiter.svg?branch=master)](https://travis-ci.org/dadamssg/Arbiter)
[![Coverage Status](https://coveralls.io/repos/dadamssg/arbiter/badge.png?branch=master)](https://coveralls.io/r/dadamssg/arbiter?branch=master)

> **Note:** Arbiter assumes [Symfony's security component](https://packagist.org/packages/symfony/security) ACL's have already been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

## Documentation

Arbiter makes granting users different permissions for specific objects easy. It does this by hiding the complexity of working with Symfony's security component to manipulate ACL's.

You don't need to worry about: ACL's, ACE's, object identities, security identities, mask builders, etc.

Read the [generated API documentation](http://dadamssg.github.io/arbiter/) or view code samples below.

## Updating permissions

```php
// get the arbiter
$arbiter = $this->get('object.arbiter');

// get a user
$user = $this->get('security.context')->getToken()->getUser();

// get an entity
$document = $this->get('document.repository')->find(1);

// focus the arbiter on an entity
$arbiter->setObject($document);

// get the current permissions the user has for the $document
$permissions = $arbiter->getPermissions($user);

// add permissions
$permissions
    ->add('VIEW')
    ->add('EDIT');

// update permissions for user
$arbiter->updatePermissions($user, $permissions);

// remove permissions
$permissions->remove('EDIT');

// update permissions for user
$arbiter->updatePermissions($user, $permissions);
```

> **Note:** Arbiter uses Symfony's [BasicPermissionMap](https://github.com/symfony/Security/blob/master/Acl/Permission/BasicPermissionMap.php) internally. Out of the box, the Arbiter is limited to those permissions and is case-sensitive.

## Checking permissions

```php
// get a permissions object
$permissions = $arbiter->newPermissions(array('EDIT'));

// focus the arbiter on the entity
$arbiter->setObject($project);

// check permissions
$canEdit = $arbiter->isGranted($user, $permissions); // bool
```

## Suggestions

Only check against a single permission even though the `Permissions` object can contain several. The security component, by default, will grant access if the user has any one of the permissions contained in the `Permissions` object. For example:

```php
// get a permissions object
$permissions = $arbiter->newPermissions(array('EDIT', 'OPERATOR'));

// focus the arbiter on the entity
$arbiter->setObject($document);

// check permissions
$granted = $arbiter->isGranted($user, $permissions); // bool
```

If the user has an ACE entry for either `EDIT` or `OPERATOR`, access is granted. Checking against multiple permissions at the same time can cause confusion.

# Gotchas

Because of the bitmask implementation of Symfony's ACL system, removing permissions isn't as straight-forward as one might think. Consider the following example:

```php
// get a permissions object
$permissions = $arbiter->newPermissions(array('OPERATOR'));

// focus the arbiter on the entity
$arbiter->setObject($project);

// grant permissions
$arbiter->grant($user, $permissions);

// time passes and you need to adjust the user's permissions.

// get the permissions the $user currently has for the $project
$permissions = $arbiter->getPermissions($user);

// remove the DELETE permission
$permissions->remove('DELETE');

// update permissions
$arbiter->grant($user, $permissions);
```

Because the `OPERATOR` permission infers the `DELETE` permission in Symfony's security system,
one might think you can simply remove it and assume the `$user` has every CRUD permission except `DELETE`.
This is false and the wrong way to think about it. The `$user` will still have the `OPERATOR` permission which
still includes the `DELETE` permission.

Instead, a better approach would be to create a new `Permissions` object with only the explicit permissions the `$user` should
hold. This new `Permissions` object should be used in a `$arbiter->updatePermissions($user, $permissions)` method call.

## Register Arbiter in Symfony's container

```yml
# services.yml

services:
    object.arbiter:
        class: ProgrammingAreHard\Arbiter\Domain\ObjectArbiter
        arguments:[@security.acl.provider]
```

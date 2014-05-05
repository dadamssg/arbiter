## Arbiter

[![Build Status](https://travis-ci.org/dadamssg/arbiter.svg?branch=master)](https://travis-ci.org/dadamssg/Arbiter)

> **Note:** Arbiter assumes [Symfony's security component](https://packagist.org/packages/symfony/security) ACL's have already been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

## Documentation

Arbiter makes granting users different permissions for specific objects easy. It does this by hiding the complexity of working with Symfony's security component to manipulate ACL's.

You don't need to worry about: ACL's, ACE's, object identities, security identities, mask builders, etc.

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

## Register Arbiter in Symfony's container

```yml
# services.yml

services:
    object.arbiter:
        class: ProgrammingAreHard\Arbiter\Domain\ObjectArbiter
        arguments:[@security.acl.provider]
```
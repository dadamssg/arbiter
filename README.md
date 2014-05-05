## Arbiter

[![Build Status](https://travis-ci.org/dadamssg/arbiter.svg?branch=master)](https://travis-ci.org/dadamssg/Arbiter)

> **Note:** Arbiter assumes [Symfony's security component](https://packagist.org/packages/symfony/security) ACL's have already been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

## Documentation

Arbiter makes granting users different permissions for specific objects easy. It does this by hiding the complexity of working with Symfony's security component to manipulate ACL's.

You don't need to worry about: ACL's, ACE's, object identities, security identies, mask builders, etc. 

## Updating permissions

```php
use ProgrammingAreHard\Arbiter\Domain\ObjectArbiter as Arbiter;

// get the acl provider from the container
$aclProvider = $this->get('security.acl.provider');

// instantiate the Arbiter
$arbiter = new Arbiter($aclProvider);

// get a user
$user = $this->get('security.context')->getToken()->getUser();

// get an entity
$document = $this->get('document.repository')->find(1);

// focus the arbiter on the $document
$arbiter->setObject($document);

// get the current permissions the user has access for the $document
$permissions = $arbiter->getPermissions($user);

// add permissions
$permissions
    ->add('VIEW')
    ->add('EDIT');

// update permissions for user
$arbiter->updatePermissions($user, $permissions);

// remove permissions
$permissions->remove('EDIT');

$arbiter->updatePermissions($user, $permissions);
```

## Checking permissions

```php
// check single permission
$arbiter->setObject($project);

$permissions = new Permissions;
$permissions->add('EDIT');

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

> **Note:** Arbiter uses the MaskBuilder internally. This means, out of the box, it is limited to the [MaskBuilder's permissions](https://github.com/symfony/Security/blob/master/Acl/Permission/MaskBuilder.php#L20).
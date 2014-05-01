## Arbiter

[![Build Status](https://travis-ci.org/dadamssg/Arbiter.svg?branch=master)](https://travis-ci.org/dadamssg/Arbiter)

> **Note:** Arbiter assumes [Symfony's security component](https://packagist.org/packages/symfony/security) ACL's have already been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

## Documentation

Arbiter makes granting users different permissions for specific objects easy. It does this by hiding the complexity of working with Symfony's security component to manipulate ACL's.

You don't need to worry about: ACL's, ACE's, object identities, security identies, mask builders, etc. 

## Granting permisssions

```php
// get the acl provider from the container
$aclProvider = $this->get('security.acl.provider');

// instantiate the Arbiter
$arbiter = new PermissionsArbiter($aclProvider);

// get a user
$user = $this->get('security.context')->getToken()->getUser();

// get an entity with an id
$document = $this->get('document.repository')->getDocument(1);

// grant single permission
$arbiter
    ->setObject($document)
    ->setPermissions('master')
    ->grant($user);

// or grant multiple permissions
$arbiter
    ->setObject($document)
    ->setPermissions(['view', 'edit'])
    ->grant($user);
```

> **Note:** Granting always adds to existing permissions if there are any. It never takes any away.

## Revoking permissions

```php
$arbiter
    ->setObject($task)
    ->setPermissions('edit')
    ->revoke($user);
```

## Checking permissions

```php
$arbiter
    ->setObject($project)
    ->setPermissions('edit');
    
$isGranted = $arbiter->isGranted($user); // true or false
```

## Register Arbiter in Symfony's container

```yml
# services.yml

services:
    permissions.arbiter:
        class: ProgrammingAreHard\ResourceBundle\Security\AclPermissionsArbiter
        arguments:[@security.acl.provider]
```

> **Note:** Arbiter uses the MaskBuilder internally. This means, out of the box, it is limited to the [MaskBuilder's permissions](https://github.com/symfony/Security/blob/master/Acl/Permission/MaskBuilder.php#L20).
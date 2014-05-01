## Arbiter

[![Build Status](https://travis-ci.org/dadamssg/Arbiter.svg?branch=master)](https://travis-ci.org/dadamssg/Arbiter)

> **Note:** Arbiter assumes [Symfony's security component](https://packagist.org/packages/symfony/security) ACL's have already been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

## Documentation

Arbiter makes granting users different permissions on resources easy. It does this by hiding the complexity of working with Symfony's security component to manipulate ACL's. 

You don't need to worry about: ACL's, ACE's, object identities, security identies, mask builders, etc. 

---

> Arbiter assumes ACL's have been [set up](http://symfony.com/doc/current/cookbook/security/acl.html).

---

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

$arbiter
    ->setObject($document)
    ->setPermissions(MaskBuilder::MASK_OWNER)
//  ->setPermissions([MaskBuilder::MASK_VIEW, MaskBuilder::MASK_EDIT]) or multiple permissions
    ->grant($user);
```

---

## Revoking permissions

```php
$arbiter
    ->setObject($task)
    ->setPermissions(MaskBuilder::MASK_EDIT)
    ->revoke($user);
```

---

## Checking permissions

```php
$arbiter
    ->setObject($project)
    ->setPermissions(MaskBuilder::MASK_EDIT);
    
$isGranted = $arbiter->isGranted($user); // true or false
```

---

## Register Arbiter in Symfony's container

```yml
# services.yml

services:
    permissions.arbiter:
        class: ProgrammingAreHard\ResourceBundle\Security\AclPermissionsArbiter
        arguments:[@security.acl.provider]
```
    
## Generating api docs
```
apigen --source src --source vendor/symfony/security/Symfony/Component/Security/Acl/Model --source vendor/symfony/security/Symfony/Component/Security/Acl/Exception --source vendor/symfony/security/Symfony/Component/Security/Acl/Permission --source vendor/symfony/security/Symfony/Component/Security/Core/User --skip-doc-path "vendor/*" --exclude "*/tests/*" --exclude "*/Tests/*" --destination docs/ --title "Arbiter"
```
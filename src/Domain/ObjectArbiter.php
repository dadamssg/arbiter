<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsInterface;
use ProgrammingAreHard\Arbiter\Model\ObjectArbiterInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsTransformerInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectArbiter implements ObjectArbiterInterface
{
    /**
     * @var ObjectIdentityInterface
     */
    protected $objectIdentity;

    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var PermissionsTransformerInterface
     */
    protected $permissionsTransformer;

    /**
     * @var IdentityFactoryInterface
     */
    protected $identityFactory;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface $aclProvider
     * @param PermissionsTransformerInterface $permissionsTransformer
     * @param IdentityFactoryInterface $identityFactory
     */
    public function __construct(
        MutableAclProviderInterface $aclProvider,
        PermissionsTransformerInterface $permissionsTransformer = null,
        IdentityFactoryInterface $identityFactory = null
    ) {
        $this->aclProvider = $aclProvider;
        $this->permissionsTransformer = $permissionsTransformer ? : new PermissionsTransformer;
        $this->identityFactory = $identityFactory ? : new IdentityFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setObject($object)
    {
        $this->objectIdentity = $this->identityFactory->getObjectIdentity($object);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newPermissions(array $permissions = array())
    {
        return $this->permissionsTransformer->newPermissions($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(UserInterface $user)
    {
        $this->ensureObjectIdentityPresent();

        $userIdentity = $this->identityFactory->getUserIdentity($user);

        $mask = 0;

        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
            $indexedAce = $this->resolveIndexedAce($acl, $userIdentity);
            $mask = $indexedAce->getAce()->getMask();
        } catch (AclNotFoundException $e) {
        } catch (NoAceFoundException $e) {
        }

        return $this->permissionsTransformer->maskToPermissions($mask);
    }


    /**
     * {@inheritdoc}
     */
    public function updatePermissions(UserInterface $user, PermissionsInterface $permissions)
    {
        $this->ensureObjectIdentityPresent();

        $userIdentity = $this->identityFactory->getUserIdentity($user);

        $mask = $this->permissionsTransformer->permissionsToMask($permissions);

        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($this->objectIdentity);
        }

        try {
            $indexedAce = $this->resolveIndexedAce($acl, $userIdentity);

            $index = $indexedAce->getIndex();

            if ($mask > 0) {
                $acl->updateObjectAce($index, $mask);
            } else {
                $acl->deleteObjectAce($index);
            }
        } catch (NoAceFoundException $e) {
            $acl->insertObjectAce($userIdentity, $mask);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(UserInterface $user, PermissionsInterface $permissions)
    {
        if (0 === count($permissions)) {
            return true;
        }

        $this->ensureObjectIdentityPresent();

        $userIdentity = $this->identityFactory->getUserIdentity($user);

        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
            $masks = $this->permissionsTransformer->permissionsToMasks($permissions);
            return $acl->isGranted($masks, array($userIdentity));
        } catch (AclNotFoundException $e) {
        } catch (NoAceFoundException $e) {
        }

        return false;
    }

    /**
     * Find the ACE from the ACL and security identity.
     *
     * @param AclInterface $acl
     * @param SecurityIdentityInterface $sid
     * @return \ProgrammingAreHard\Arbiter\Domain\IndexedAce
     * @throws \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    private function resolveIndexedAce(AclInterface $acl, SecurityIdentityInterface $sid)
    {
        foreach ($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($sid)) {
                return new IndexedAce($index, $ace);
            }
        }

        throw new NoAceFoundException;
    }

    /**
     * Guard against no object identity.
     *
     * @throws \InvalidArgumentException
     */
    private function ensureObjectIdentityPresent()
    {
        if (!$this->objectIdentity) {
            throw new \InvalidArgumentException(sprintf(
                '%s needs resource to grant and check permissions.',
                get_class($this)
            ));
        }
    }
} 
<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use ProgrammingAreHard\Arbiter\Model\MaskAggregatorInterface;
use ProgrammingAreHard\Arbiter\Model\ObjectPermissionsArbiterInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectPermissionsArbiter implements ObjectPermissionsArbiterInterface
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var ObjectIdentityInterface
     */
    private $objectIdentity;

    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var IndexedAceResolverInterface
     */
    private $aceResolver;

    /**
     * @var IdentityFactoryInterface
     */
    private $identityFactory;

    /**
     * @var MaskAggregator
     */
    private $maskAggregator;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface $aclProvider
     * @param IndexedAceResolverInterface $aceResolver
     * @param IdentityFactoryInterface $identityFactory
     * @param MaskAggregatorInterface $maskAggregator
     */
    public function __construct(
        MutableAclProviderInterface $aclProvider,
        IndexedAceResolverInterface $aceResolver = null,
        IdentityFactoryInterface $identityFactory = null,
        MaskAggregatorInterface $maskAggregator = null
    ) {
        $this->aclProvider = $aclProvider;
        $this->aceResolver = $aceResolver ? : new IndexedAceResolver;
        $this->identityFactory = $identityFactory ? : new IdentityFactory;
        $this->maskAggregator = $maskAggregator ? : new MaskAggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function setObject($object)
    {
        $this->objectIdentity = $this->identityFactory->getObjectIdentity($object);
        $this->object = $object;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPermissions($permissions)
    {
        $this->maskAggregator->setPermissions($permissions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function grant(UserInterface $user)
    {
        $this->ensureObjectPresence();

        $userIdentity = $this->identityFactory->getUserIdentity($user);
        $this->maskAggregator->setMode(MaskAggregator::MASK_ADD);

        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($this->objectIdentity);
        }

        try {
            $indexedAce = $this->aceResolver->resolveIndexedAce($acl, $userIdentity);

            $initialMask = $indexedAce->getAce()->getMask();
            $mask = $this->maskAggregator->build($initialMask);

            $this->updateOrDeleteAce($acl, $mask, $indexedAce->getIndex());
        } catch (NoAceFoundException $e) {
            $acl->insertObjectAce($userIdentity, $this->maskAggregator->build());
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(UserInterface $user)
    {
        $this->ensureObjectPresence();

        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
            $userIdentity = $this->identityFactory->getUserIdentity($user);
            $indexedAce = $this->aceResolver->resolveIndexedAce($acl, $userIdentity);

            $initialMask = $indexedAce->getAce()->getMask();
            $this->maskAggregator->setMode(MaskAggregator::MASK_REMOVE);
            $mask = $this->maskAggregator->build($initialMask);

            $this->updateOrDeleteAce($acl, $mask, $indexedAce->getIndex());
            $this->aclProvider->updateAcl($acl);
        } catch (AclNotFoundException $e) {
        } catch (NoAceFoundException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(UserInterface $user)
    {
        $this->ensureObjectPresence();

        $this->maskAggregator->setMode(MaskAggregator::MASK_ADD);

        if ($masks = $this->maskAggregator->getAllMasks($this->object)) {

            try {
                $acl = $this->aclProvider->findAcl($this->objectIdentity);
                $userIdentity = $this->identityFactory->getUserIdentity($user);
                return $acl->isGranted($masks, array($userIdentity));
            } catch (AclNotFoundException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update/delete the ace depending on mask.
     *
     * @param MutableAclInterface $acl
     * @param int $mask
     * @param int $index
     */
    private function updateOrDeleteAce(MutableAclInterface $acl, $mask, $index)
    {
        if (0 === $mask) {
            $acl->deleteObjectAce($index);
        } else {
            $acl->updateObjectAce($index, $mask);
        }
    }

    /**
     * Guard against no object.
     *
     * @throws \InvalidArgumentException
     */
    private function ensureObjectPresence()
    {
        if (!$this->object || !$this->objectIdentity) {
            throw new \InvalidArgumentException(sprintf(
                '%s needs object to grant, revoke, and check permissions.',
                get_class($this)
            ));
        }
    }
} 
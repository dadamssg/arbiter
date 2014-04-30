<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use ProgrammingAreHard\Arbiter\Model\MaskAggregatorInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsArbiterInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionsArbiter implements PermissionsArbiterInterface
{
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
    private $masksAggregator;

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
        $this->aceResolver = $aceResolver ? $aceResolver : new IndexedAceResolver;
        $this->identityFactory = $identityFactory ? $identityFactory : new IdentityFactory;
        $this->masksAggregator = $maskAggregator ? $maskAggregator : new MaskAggregator;
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
    public function setPermissions($permissions)
    {
        $this->masksAggregator->setPermissions($permissions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function grant(UserInterface $user)
    {
        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($this->objectIdentity);
        }

        $userIdentity = $this->identityFactory->getUserIdentity($user);

        try {

            $indexedAce = $this->aceResolver->resolveIndexedAce($acl, $userIdentity);

            $initialMask = $indexedAce->getAce()->getMask();
            $mask = $this->masksAggregator->build($initialMask);

            $acl->updateObjectAce($indexedAce->getIndex(), $mask);
        } catch (NoAceFoundException $e) {
            $acl->insertObjectAce($userIdentity, $this->masksAggregator->build());
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(UserInterface $user)
    {
        try {
            $acl = $this->aclProvider->findAcl($this->objectIdentity);
            $userIdentity = $this->identityFactory->getUserIdentity($user);
            $indexedAce = $this->aceResolver->resolveIndexedAce($acl, $userIdentity);

            $initialMask = $indexedAce->getAce()->getMask();
            $mask = $this->masksAggregator
                ->setMode(MaskAggregator::MASK_REMOVE)
                ->build($initialMask);

            $acl->updateObjectAce($indexedAce->getIndex(), $mask);
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
        if ($masks = $this->masksAggregator->getAllMasks(new \stdClass)) {

            try {
                $acl = $this->aclProvider->findAcl($this->objectIdentity);
                $userIdentity = $this->identityFactory->getUserIdentity($user);
                return $acl->isGranted($masks, array($userIdentity));
            } catch (AclNotFoundException $e) {
            }

            return false;
        }

        return true;
    }
} 
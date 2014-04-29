<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use ProgrammingAreHard\Arbiter\Model\MasksAggregatorInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsArbiterInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionsArbiter implements PermissionsArbiterInterface
{
    /**
     * Object to grant/check permissions against.
     *
     * @var object
     */
    private $object;

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
     * @var MasksAggregator
     */
    private $masksAggregator;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface $aclProvider
     * @param IndexedAceResolverInterface $aceResolver
     * @param IdentityFactoryInterface $identityFactory
     * @param MasksAggregatorInterface $maskAggregator
     */
    public function __construct(
        MutableAclProviderInterface $aclProvider,
        IndexedAceResolverInterface $aceResolver = null,
        IdentityFactoryInterface $identityFactory = null,
        MasksAggregatorInterface $maskAggregator = null
    ) {
        $this->aclProvider = $aclProvider;
        $this->aceResolver = $aceResolver ? $aceResolver : new IndexedAceResolver;
        $this->identityFactory = $identityFactory ? $identityFactory : new IdentityFactory;
        $this->masksAggregator = $maskAggregator ? $maskAggregator : new MasksAggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                'PermissionsArbiter expected object. %s given',
                gettype($object)
            ));
        }

        $this->object = $object;

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
        $acl = $this->getAcl($this->object);
        $userIdentity = $this->identityFactory->getUserIdentity($user);

        try {

            $indexedAce = $this->aceResolver->resolveIndexedAce($acl, $userIdentity);

            $initialMask = $indexedAce->getAce()->getMask();
            $mask = $this->masksAggregator->build($initialMask);

            $acl->updateObjectAce($indexedAce->getIndex(), $mask);
            $this->aclProvider->updateAcl($acl);

        } catch (NoAceFoundException $e) {

            $acl->insertObjectAce($userIdentity, $this->masksAggregator->build());
            $this->aclProvider->updateAcl($acl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(UserInterface $user)
    {
        // TODO: Implement revoke() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(UserInterface $user)
    {
        // TODO: Implement isGranted() method.
    }

    /**
     * Get resource's ACL.
     *
     * @param object $object
     * @return \Symfony\Component\Security\Acl\Model\MutableAclInterface
     */
    private function getAcl($object)
    {
        $objectIdentity = $this->identityFactory->getObjectIdentity($object);

        try {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            $acl = $this->aclProvider->createAcl($objectIdentity);
        }

        return $acl;
    }
} 
<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use Symfony\Component\Security\Acl\Model\EntryInterface;

class IndexedAce
{
    /**
     * @var int
     */
    private $index;

    /**
     * @var \Symfony\Component\Security\Acl\Model\EntryInterface
     */
    private $ace;

    /**
     * Constructor.
     *
     * @param int $index
     * @param EntryInterface $ace
     */
    public function __construct($index, EntryInterface $ace)
    {
        $this->index = $index;
        $this->ace = $ace;
    }

    /**
     * Get the ACE's index.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get the ACE.
     *
     * @return EntryInterface
     */
    public function getAce()
    {
        return $this->ace;
    }
} 
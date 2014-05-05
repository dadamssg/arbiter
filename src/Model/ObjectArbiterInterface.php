<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface ObjectArbiterInterface extends ArbiterInterface
{
    /**
     * Set the resource.
     *
     * @param object $object
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setObject($object);
}
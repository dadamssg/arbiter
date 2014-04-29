<?php

namespace ProgrammingAreHard\Arbiter\Tests\stubs;

class Entity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
} 
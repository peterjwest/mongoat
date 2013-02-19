<?php

namespace WhiteOctober\MongoatBundle\Tests\Mocks;

class Collection
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

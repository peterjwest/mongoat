<?php

namespace WhiteOctober\MongoatBundle\Tests\Mocks;

class Connection
{
    public function collection($name)
    {
        return new Collection($name);
    }
}

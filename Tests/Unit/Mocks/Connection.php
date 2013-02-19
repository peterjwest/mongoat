<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit\Mocks;

class Connection
{
    public function collection($name)
    {
        return new Collection($name);
    }
}

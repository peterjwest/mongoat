<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;

class MongoatTest extends \PHPUnit_Framework_TestCase
{
    protected $class = 'WhiteOctober\MongoatBundle\Core\Mongoat';

    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $connection = new Connection('localhost', 'mongoat_tests');
        $this->mongoat->addConnection('local', $connection);
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');
    }

    public function testGetConnection()
    {
        $connection = $this->mongoat->connection();

        $this->assertTrue(is_object($connection));
        $this->assertSame('WhiteOctober\MongoatBundle\Core\Connection', get_class($connection));
    }

    public function testGetCollection()
    {
        $collection = $this->mongoat->collection('User');

        $this->assertTrue(is_object($collection));
        $this->assertSame(
            'MongoCollection',
            get_class($collection)
        );
        $this->assertSame('User', $collection->getName());
    }

    public function testCreateModel()
    {
        $user = $this->mongoat->create('User');

        $this->assertTrue(is_object($user));
        $this->assertSame(
            'WhiteOctober\MongoatBundle\Tests\Fixtures\User',
            get_class($user)
        );
    }
}

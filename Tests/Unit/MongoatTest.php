<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema\Schema;
use WhiteOctober\MongoatBundle\Tests\Mocks\Connection;

class MongoatTest extends \PHPUnit_Framework_TestCase
{
    protected $class = 'WhiteOctober\MongoatBundle\Core\Mongoat';

    public function setUp()
    {
        $this->mongoat = new Mongoat();
    }

    public function testDefaultConnectionDoesNotExist()
    {
        $this->setExpectedException('Exception', "No connections found in $this->class");

        $this->mongoat->connection();
    }

    public function testConnectionDoesNotExist()
    {
        $this->setExpectedException('Exception', "Connection 'local' not found in $this->class");

        $this->mongoat->connection('local');
    }

    public function testGetConnection()
    {
        $connection = new Connection();
        $this->mongoat->addConnection('local', $connection);

        $this->assertSame($connection, $this->mongoat->connection());
    }

    public function testGetCollection()
    {
        $connection = new Connection();
        $this->mongoat->addConnection('local', $connection);
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $collection = $this->mongoat->collection('User');

        $this->assertTrue(is_object($collection));
        $this->assertSame(
            'WhiteOctober\MongoatBundle\Tests\Mocks\Collection',
            get_class($collection)
        );
        $this->assertSame('User', $collection->getName());
    }

    public function testCreateModel()
    {
        $connection = new Connection();
        $this->mongoat->addConnection('local', $connection);
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $user = $this->mongoat->create('User');
        $this->assertTrue(is_object($user));
        $this->assertSame(
            'WhiteOctober\MongoatBundle\Tests\Fixtures\User',
            get_class($user)
        );
    }
}

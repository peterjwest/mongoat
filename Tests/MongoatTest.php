<?php

namespace WhiteOctober\MongoatBundle\Tests;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema;
use WhiteOctober\MongoatBundle\Tests\Mocks\Connection;

use PHPUnit_Framework_TestCase;

class MongoatTest extends PHPUnit_Framework_TestCase
{
    protected $class = 'WhiteOctober\MongoatBundle\Core\Mongoat';

    public function setUp()
    {
        $this->mongoat = new Mongoat();
        //$this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');
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
}

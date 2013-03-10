<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Collar;

class HasManyRelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $this->mongoat->addConnection('local', new Connection('localhost', 'mongoat_test'));
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->mongoat->delete('Cat')->all();
        $this->mongoat->delete('Collar')->all();

        $this->cat = new Cat();
        $this->cat->mongoat($this->mongoat);

        $this->collar = new Collar();
        $this->collar->mongoat($this->mongoat);
    }

    public function testCachedRelationshipSetByObject()
    {
        $this->cat->collars($this->collar);

        $collars = $this->cat->collars();
        $this->assertEquals(1, count($collars));
        $this->assertSame($this->collar, $collars[0]);
        $this->assertSame($this->cat, $collars[0]->cat());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->collar->catId($this->cat->id());
        $this->cat->save();
        $this->collar->save();

        $collars = $this->cat->collars();

        $this->assertEquals(1, count($collars));
        $this->assertEquals($this->collar->id(), $collars[0]->id());
        $this->assertNotSame($this->collar, $collars[0]);
        $this->assertSame($this->cat, $collars[0]->cat());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->cat->collars($this->collar);
        $this->cat->save();
        $this->collar->save();

        $cat = $this->mongoat->find('Cat')->one();

        $collars = $cat->collars();
        $this->assertEquals(1, count($collars));
        $this->assertSame($this->collar->id(), $collars[0]->id());
        $this->assertNotSame($this->collar, $collars[0]);
        $this->assertSame($cat, $collars[0]->cat());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->cat->collars($this->collar);
        $this->cat->save();
        $this->collar->save();

        $collars = $this->cat->collars();
        $this->assertEquals(1, count($collars));
        $this->assertSame($this->collar, $collars[0]);
        $this->assertSame($this->cat, $collars[0]->cat());
    }
}

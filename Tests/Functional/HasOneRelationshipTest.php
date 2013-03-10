<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Tail;

class HasOneRelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $this->mongoat->addConnection('local', new Connection('localhost', 'mongoat_test'));
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->mongoat->delete('Cat')->all();
        $this->mongoat->delete('Tail')->all();

        $this->cat = new Cat();
        $this->cat->mongoat($this->mongoat);

        $this->tail = new Tail();
        $this->tail->mongoat($this->mongoat);
    }

    public function testCachedRelationshipSetByObject()
    {
        $this->cat->tail($this->tail);

        $this->assertSame($this->tail, $this->cat->tail());
        $this->assertSame($this->cat, $this->cat->tail()->cat());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->tail->catId($this->cat->id());
        $this->cat->save();
        $this->tail->save();

        $this->assertEquals($this->tail->id(), $this->cat->tail()->id());
        $this->assertNotSame($this->tail, $this->cat->tail());
        $this->assertSame($this->cat, $this->cat->tail()->cat());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->cat->tail($this->tail);
        $this->cat->save();
        $this->tail->save();

        $cat = $this->mongoat->find('Cat')->one();

        $this->assertSame($this->tail->id(), $cat->tail()->id());
        $this->assertNotSame($this->tail, $cat->tail());
        $this->assertSame($cat, $cat->tail()->cat());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->cat->tail($this->tail);
        $this->cat->save();
        $this->tail->save();

        $this->assertSame($this->tail, $this->cat->tail());
        $this->assertSame($this->cat, $this->cat->tail()->cat());
    }
}

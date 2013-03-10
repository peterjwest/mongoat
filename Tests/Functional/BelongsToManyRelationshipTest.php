<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Household;

class BelongsToManyRelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $this->mongoat->addConnection('local', new Connection('localhost', 'mongoat_test'));
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->mongoat->delete('Cat')->all();
        $this->mongoat->delete('Household')->all();

        $this->cat = new Cat();
        $this->cat->mongoat($this->mongoat);

        $this->household = new Household();
        $this->household->mongoat($this->mongoat);
    }

    public function testCachedRelationshipSetByObject()
    {
        $this->cat->households($this->household);

        $households = $this->cat->households();
        $this->assertEquals(1, count($households));
        $this->assertSame($this->household, $households[0]);
        $this->assertSame($this->cat, $households[0]->cat());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->cat->householdsId($this->household->id());
        $this->cat->save();
        $this->household->save();

        $households = $this->cat->households();

        $this->assertEquals(1, count($households));
        $this->assertEquals($this->household->id(), $households[0]->id());
        $this->assertNotSame($this->household, $households[0]);
        $this->assertSame($this->cat, $households[0]->cat());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->cat->households($this->household);
        $this->cat->save();
        $this->household->save();

        $cat = $this->mongoat->find('Cat')->one();

        $households = $cat->households();
        $this->assertEquals(1, count($households));
        $this->assertSame($this->household->id(), $households[0]->id());
        $this->assertNotSame($this->household, $households[0]);
        $this->assertSame($cat, $households[0]->cat());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->cat->households($this->household);
        $this->cat->save();
        $this->household->save();

        $households = $this->cat->households();
        $this->assertEquals(1, count($households));
        $this->assertSame($this->household, $households[0]);
        $this->assertSame($this->cat, $households[0]->cat());
    }
}

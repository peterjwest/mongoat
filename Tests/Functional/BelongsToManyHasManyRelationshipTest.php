<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Street;

class BelongsToManyHasManyRelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $this->mongoat->addConnection('local', new Connection('localhost', 'mongoat_test'));
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->mongoat->delete('Cat')->all();
        $this->mongoat->delete('street')->all();

        $this->cat = new Cat();
        $this->cat->mongoat($this->mongoat);

        $this->street = new street();
        $this->street->mongoat($this->mongoat);
    }

    public function testCachedRelationshipSetByObject()
    {
        $this->cat->streets($this->street);

        $streets = $this->cat->streets();
        $this->assertEquals(1, count($streets));
        $this->assertSame($this->street, $streets[0]);
        $this->assertSame(array($this->cat), $streets[0]->cats());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->cat->streetsId($this->street->id());
        $this->cat->save();
        $this->street->save();

        $streets = $this->cat->streets();

        $this->assertEquals(1, count($streets));
        $this->assertEquals($this->street->id(), $streets[0]->id());
        $this->assertNotSame($this->street, $streets[0]);
        $this->assertSame(array($this->cat), $streets[0]->cats());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->cat->streets($this->street);
        $this->cat->save();
        $this->street->save();

        $cat = $this->mongoat->find('Cat')->one();

        $streets = $cat->streets();
        $this->assertEquals(1, count($streets));
        $this->assertSame($this->street->id(), $streets[0]->id());
        $this->assertNotSame($this->street, $streets[0]);
        $this->assertSame(array($cat), $streets[0]->cats());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->cat->streets($this->street);
        $this->cat->save();
        $this->street->save();

        $streets = $this->cat->streets();
        $this->assertEquals(1, count($streets));
        $this->assertSame($this->street, $streets[0]);
        $this->assertSame(array($this->cat), $streets[0]->cats());
    }
}

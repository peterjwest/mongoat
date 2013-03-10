<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Human;

class BelongsToRelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $this->mongoat->addConnection('local', new Connection('localhost', 'mongoat_test'));
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->mongoat->delete('Cat')->all();
        $this->mongoat->delete('Human')->all();

        $this->cat = new Cat();
        $this->cat->mongoat($this->mongoat);

        $this->human = new Human();
        $this->human->mongoat($this->mongoat);
    }

    public function testCachedRelationshipSetByObject()
    {
        $this->cat->owner($this->human);

        $this->assertSame($this->human, $this->cat->owner());
        $this->assertSame($this->cat, $this->cat->owner()->cat());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->cat->ownerId($this->human->id());
        $this->cat->save();
        $this->human->save();

        $this->assertEquals($this->human->id(), $this->cat->owner()->id());
        $this->assertNotSame($this->human, $this->cat->owner());
        $this->assertSame($this->cat, $this->cat->owner()->cat());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->cat->owner($this->human);
        $this->cat->save();
        $this->human->save();

        $cat = $this->mongoat->find('Cat')->one();

        $this->assertSame($this->human->id(), $cat->owner()->id());
        $this->assertNotSame($this->human, $cat->owner());
        $this->assertSame($cat, $cat->owner()->cat());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->cat->owner($this->human);
        $this->cat->save();
        $this->human->save();

        $this->assertSame($this->human, $this->cat->owner());
        $this->assertSame($this->cat, $this->cat->owner()->cat());
    }
}

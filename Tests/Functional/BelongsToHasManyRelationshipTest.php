<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Collar;

class BelongsToHasManyRelationshipTest extends \PHPUnit_Framework_TestCase
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
        $this->collar->cat($this->cat);

        $this->assertSame($this->cat, $this->collar->cat());
        $this->assertSame(array($this->collar), $this->collar->cat()->collars());
    }

    public function testPersistedRelationshipSetById()
    {
        $this->collar->catId($this->cat->id());
        $this->collar->save();
        $this->cat->save();

        $this->assertEquals($this->cat->id(), $this->collar->cat()->id());
        $this->assertNotSame($this->cat, $this->collar->cat());
        $this->assertSame(array($this->collar), $this->collar->cat()->collars());
    }

    public function testPersistedRelationshipSetByObject()
    {
        $this->collar->cat($this->cat);
        $this->collar->save();
        $this->cat->save();

        $collar = $this->mongoat->find('Collar')->one();

        $this->assertSame($this->cat->id(), $collar->cat()->id());
        $this->assertNotSame($this->cat, $collar->cat());
        $this->assertSame(array($collar), $collar->cat()->collars());
    }

    public function testPersistedCachedRelationshipSetByObject()
    {
        $this->collar->cat($this->cat);
        $this->collar->save();
        $this->cat->save();

        $this->assertSame($this->cat, $this->collar->cat());
        $this->assertSame(array($this->collar), $this->collar->cat()->collars());
    }
}

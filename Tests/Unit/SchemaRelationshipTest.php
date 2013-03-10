<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema\Schema;
use WhiteOctober\MongoatBundle\Tests\Fixtures\Cat;
use WhiteOctober\MongoatBundle\Core\Schema\Field\FieldSchema;
use WhiteOctober\MongoatBundle\Core\Schema\Relationship\RelationshipSchema;

use PHPUnit_Framework_TestCase;

class SchemaRelationshipTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mongoat = new Mongoat();
    }

    public function setUp()
    {
        $this->model = new Cat();
        $this->model->mongoat($this->mongoat);
        $this->schema = $this->model->schema();
    }

    public function testCorrectFieldsExist()
    {
        $this->assertEquals(4, count($this->schema->fields()));

        $this->assertTrue($this->schema->field('ownerId') instanceof FieldSchema);
        $this->assertTrue($this->schema->field('householdsId') instanceof FieldSchema);

        $this->assertNull($this->schema->field('apple'));
        $this->assertNull($this->schema->field('banana'));
        $this->assertNull($this->schema->field('strawberry'));
    }

    public function testCorrectRelationshipsExist()
    {
        $this->assertEquals(count($this->schema->relationships()), 5);

        $this->assertTrue($this->schema->relationship('owner') instanceof RelationshipSchema);
        $this->assertTrue($this->schema->relationship('tail') instanceof RelationshipSchema);
        $this->assertTrue($this->schema->relationship('collars') instanceof RelationshipSchema);
        $this->assertTrue($this->schema->relationship('households') instanceof RelationshipSchema);
        $this->assertTrue($this->schema->relationship('streets') instanceof RelationshipSchema);

        $this->assertNull($this->schema->relationship('apple'));
        $this->assertNull($this->schema->relationship('banana'));
        $this->assertNull($this->schema->relationship('strawberry'));
    }

    public function testCorrectRelationshipsAreMultiple()
    {
        $this->assertEquals(false, $this->schema->relationship('owner')->multiple());
        $this->assertEquals(false, $this->schema->relationship('tail')->multiple());
        $this->assertEquals(true, $this->schema->relationship('collars')->multiple());
        $this->assertEquals(true, $this->schema->relationship('households')->multiple());
         $this->assertEquals(true, $this->schema->relationship('streets')->multiple());
    }

    public function testRelationshipsHaveCorrectFieldName()
    {
        $this->assertEquals('ownerId', $this->schema->relationship('owner')->fieldName());
        $this->assertEquals('catId', $this->schema->relationship('tail')->fieldName());
        $this->assertEquals('catId', $this->schema->relationship('collars')->fieldName());
        $this->assertEquals('householdsId', $this->schema->relationship('households')->fieldName());
        $this->assertEquals('streetsId', $this->schema->relationship('streets')->fieldName());
    }

    public function testRelationshipsHaveCorrectForeignClass()
    {
        $this->assertEquals('Human', $this->schema->relationship('owner')->foreignClass());
        $this->assertEquals('Tail', $this->schema->relationship('tail')->foreignClass());
        $this->assertEquals('Collar', $this->schema->relationship('collars')->foreignClass());
        $this->assertEquals('Household', $this->schema->relationship('households')->foreignClass());
        $this->assertEquals('Street', $this->schema->relationship('streets')->foreignClass());
    }

    public function testCorrectRelationshipsHaveAForeignKey()
    {
        $this->assertEquals(true, $this->schema->relationship('owner')->foreignKey());
        $this->assertEquals(false, $this->schema->relationship('tail')->foreignKey());
        $this->assertEquals(false, $this->schema->relationship('collars')->foreignKey());
        $this->assertEquals(true, $this->schema->relationship('households')->foreignKey());
        $this->assertEquals(true, $this->schema->relationship('streets')->foreignKey());
    }

    public function testCorrectRelationshipsHaveAnInverse()
    {
        $this->assertEquals('cat', $this->schema->relationship('owner')->inverse());
        $this->assertEquals('cat', $this->schema->relationship('tail')->inverse());
        $this->assertEquals('cat', $this->schema->relationship('collars')->inverse());
        $this->assertEquals('cat', $this->schema->relationship('households')->inverse());
        $this->assertEquals('cats', $this->schema->relationship('streets')->inverse());
    }
}

<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema\Schema;
use WhiteOctober\MongoatBundle\Tests\Fixtures\User;
use WhiteOctober\MongoatBundle\Core\Schema\Field\FieldSchema;

use PHPUnit_Framework_TestCase;

class SchemaFieldTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mongoat = new Mongoat();
    }

    public function setUp()
    {
        $this->model = new User();
        $this->model->mongoat($this->mongoat);
        $this->schema = $this->model->schema();
    }

    public function testCorrectFieldsExist()
    {
        $this->assertEquals(count($this->schema->fields()), 12);

        $this->assertTrue($this->schema->field('_id') instanceof FieldSchema);
        $this->assertTrue($this->schema->field('name') instanceof FieldSchema);
        $this->assertTrue($this->schema->field('catNames') instanceof FieldSchema);

        $this->assertNull($this->schema->field('apple'));
        $this->assertNull($this->schema->field('banana'));
        $this->assertNull($this->schema->field('strawberry'));
    }

    public function testGetRawField()
    {
        $date = new \DateTime();

        $this->assertSame('123', $this->schema->field('anything')->get('123'));
        $this->assertSame(123.456, $this->schema->field('anything')->get(123.456));
        $this->assertSame($date, $this->schema->field('anything')->get($date));
    }

    public function testSetIdField()
    {
        $id = new \MongoId('51142e3646bd7421ed000000');

        $this->assertEquals($id, $this->schema->field('catId')->set('51142e3646bd7421ed000000'));
        $this->assertEquals($id, $this->schema->field('catId')->set($id));
    }

    public function testSetString()
    {
        $this->assertSame('blah blah blah', $this->schema->field('name')->set('blah blah blah'));
        $this->assertSame('123', $this->schema->field('name')->set(123));
    }

    public function testSetInteger()
    {
        $this->assertSame(123, $this->schema->field('count')->set(123));
        $this->assertSame(123, $this->schema->field('count')->set('123'));
        $this->assertSame(123, $this->schema->field('count')->set(123.27));
        $this->assertSame(123, $this->schema->field('count')->set(123.89));
    }

    public function testSetFloat()
    {
        $this->assertSame(123.0, $this->schema->field('value')->set(123));
        $this->assertSame(123.0, $this->schema->field('value')->set('123'));
        $this->assertSame(123.27, $this->schema->field('value')->set(123.27));
        $this->assertSame(123.89, $this->schema->field('value')->set(123.89));
    }

    public function testSetBoolean()
    {
        $this->assertSame(false, $this->schema->field('enabled')->set(false));
        $this->assertSame(true, $this->schema->field('enabled')->set('123'));
        $this->assertSame(true, $this->schema->field('enabled')->set(1));
        $this->assertSame(false, $this->schema->field('enabled')->set(0));
        $this->assertSame(false, $this->schema->field('enabled')->set(null));
    }

    public function testSetDate()
    {
        $date = new \DateTime('2012-11-01');

        $this->assertEquals($date, $this->schema->field('createdAt')->set('2012-11-01'));
        $this->assertEquals($date, $this->schema->field('createdAt')->set($date));
        $this->assertEquals($date, $this->schema->field('createdAt')->set($date->getTimestamp()));
    }

    public function testSetArrayField()
    {
        $this->assertSame(array('The Great Catsby', 'Cat-ur-bury Tails'), $this->schema->field('catNames')->set(array('The Great Catsby', 'Cat-ur-bury Tails')));
        $this->assertSame(array('Mew'), $this->schema->field('catNames')->set('Mew'));
        $this->assertSame(array('1'), $this->schema->field('catNames')->set(1));

        $this->assertSame(array(1, 2, 3), $this->schema->field('prices')->set(array(1, 2, '3')));
        $this->assertSame(array(1, 2), $this->schema->field('prices')->set(array('a' => 1, 'b' => 2)));
    }

    public function testGetIdField()
    {
        $id = '51143ceb46bd74f3ed000002';
        $id2 = '51143ceb46bd74f3ed000002';

        $this->assertSame(
            $id,
            $this->schema->field('catId')->get(new \MongoId($id))
        );

        $this->assertSame(
            array($id, $id2),
            $this->schema->field('dogIds')->get(array(new \MongoId($id), new \MongoId($id2)))
        );
    }

    public function testDateHydration()
    {
        $date = new \DateTime("2010-01-30 02:53:22");
        $mongoDate = new \MongoDate(strtotime("2010-01-30 02:53:22"));

        $this->assertEquals($mongoDate, $this->schema->field('createdAt')->dehydrate($date));
        $this->assertEquals($date, $this->schema->field('createdAt')->hydrate($mongoDate));
    }

    public function testDefaultValues()
    {
        $this->assertSame('Your name', $this->schema->field('name')->defaultValue());
        $this->assertSame(null, $this->schema->field('anything')->defaultValue());
        $this->assertSame(3, $this->schema->field('count')->defaultValue());
        $this->assertSame(true, $this->schema->field('enabled')->defaultValue());
        $this->assertSame(null, $this->schema->field('createdAt')->defaultValue());
        $this->assertSame(array(), $this->schema->field('prices')->defaultValue());
        $this->assertSame(array('Fluffy', 'Chairman Miaow'), $this->schema->field('catNames')->defaultValue());
    }

    public function testClearSchema()
    {
        $this->schema->clear();

        $this->assertEquals(count($this->schema->fields()), 1);

        $this->assertTrue($this->schema->field('_id') instanceof FieldSchema);
        $this->assertNull($this->schema->field('name'));
        $this->assertNull($this->schema->field('anything'));
        $this->assertNull($this->schema->field('prices'));
    }
}

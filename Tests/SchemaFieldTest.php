<?php

namespace WhiteOctober\MongoatBundle\Tests;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema;

use PHPUnit_Framework_TestCase;

class SchemaFieldTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mongoat = new Mongoat();
    }

    public function setUp()
    {
        $this->schema = new Schema($this->mongoat);
        $this->schema->fields(array(
            'name' => array('type' => 'string'),
            'anything' => array('type' => 'random'),
            'count' => array('type' => 'integer'),
            'createdAt' => array('type' => 'date'),
            'enabled' => array('type' => 'boolean'),
            'value' => array('type' => 'float'),
            'catId' => array('type' => 'id'),
            'catNames' => array('type' => array('array', 'string')),
            'prices' => array('type' => array('array', 'integer')),
            'loginDates' => array('type' => array('array', 'date')),
            'dogIds' => array('type' => array('array', 'id')),
        ));
    }

    public function testFieldsExist()
    {
        $this->assertEquals(count($this->schema->fields()), 12);

        $this->assertSame(array('type' => 'id'), $this->schema->field('_id'));
        $this->assertSame(array('type' => 'string'), $this->schema->field('name'));
        $this->assertSame(array('type' => array('array', 'string')), $this->schema->field('catNames'));

        $this->assertNull($this->schema->field('apple'));
        $this->assertNull($this->schema->field('banana'));
        $this->assertNull($this->schema->field('strawberry'));
    }

    public function testNonExistantField()
    {
        $this->setExpectedException('Exception', "Field 'fish' not found");
        $this->schema->filter('set', 'fish', 'blah');
    }

    public function testNonExistantFilterAction()
    {
        $this->setExpectedException('Exception', "Filter 'create' not found");
        $this->schema->filter('create', 'name', '123');
    }

    public function testUnspecifiedFilter()
    {
        $date = new \DateTime();

        $this->assertSame('123', $this->schema->filter('get', 'anything', '123'));
        $this->assertSame(123.456, $this->schema->filter('get', 'anything', 123.456));
        $this->assertSame($date, $this->schema->filter('get', 'anything', $date));
    }

    public function testSetIdField()
    {
        $id = new \MongoId('51142e3646bd7421ed000000');

        $this->assertEquals($id, $this->schema->filter('set', 'catId', '51142e3646bd7421ed000000'));
        $this->assertEquals($id, $this->schema->filter('set', 'catId', $id));
    }

    public function testSetStringFilter()
    {
        $this->assertSame('blah blah blah', $this->schema->filter('set', 'name', 'blah blah blah'));
        $this->assertSame('123', $this->schema->filter('set', 'name', 123));
    }

    public function testSetIntegerFilter()
    {
        $this->assertSame(123, $this->schema->filter('set', 'count', 123));
        $this->assertSame(123, $this->schema->filter('set', 'count', '123'));
        $this->assertSame(123, $this->schema->filter('set', 'count', 123.27));
        $this->assertSame(123, $this->schema->filter('set', 'count', 123.89));
    }

    public function testSetFloatFilter()
    {
        $this->assertSame(123.0, $this->schema->filter('set', 'value', 123));
        $this->assertSame(123.0, $this->schema->filter('set', 'value', '123'));
        $this->assertSame(123.27, $this->schema->filter('set', 'value', 123.27));
        $this->assertSame(123.89, $this->schema->filter('set', 'value', 123.89));
    }

    public function testSetBooleanFilter()
    {
        $this->assertSame(false, $this->schema->filter('set', 'enabled', false));
        $this->assertSame(true, $this->schema->filter('set', 'enabled', '123'));
        $this->assertSame(true, $this->schema->filter('set', 'enabled', 1));
        $this->assertSame(false, $this->schema->filter('set', 'enabled', 0));
        $this->assertSame(false, $this->schema->filter('set', 'enabled', null));
    }

    public function testSetDateFilter()
    {
        $date = new \DateTime('2012-11-01');

        $this->assertEquals($date, $this->schema->filter('set', 'createdAt', '2012-11-01'));
        $this->assertEquals($date, $this->schema->filter('set', 'createdAt', $date));
        $this->assertEquals($date, $this->schema->filter('set', 'createdAt', $date->getTimestamp()));
    }

    public function testSetArrayFieldFilter()
    {
        $this->assertSame(array('fluffy', 'tibbles'), $this->schema->filter('set', 'catNames', array('fluffy', 'tibbles')));
        $this->assertSame(array('tibbles'), $this->schema->filter('set', 'catNames', 'tibbles'));
        $this->assertSame(array('1'), $this->schema->filter('set', 'catNames', 1));

        $this->assertSame(array(1, 2, 3), $this->schema->filter('set', 'prices', array(1, 2, '3')));
        $this->assertSame(array(1, 2), $this->schema->filter('set', 'prices', array('a' => 1, 'b' => 2)));
    }

    public function testGetIdFieldFilter()
    {
        $id = '51143ceb46bd74f3ed000002';
        $id2 = '51143ceb46bd74f3ed000002';

        $this->assertSame(
            $id,
            $this->schema->filter('get', 'catId', new \MongoId($id))
        );

        $this->assertSame(
            array($id, $id2),
            $this->schema->filter('get', 'dogIds', array(new \MongoId($id), new \MongoId($id2)))
        );
    }

    public function testDateHydrationFilter()
    {
        $date = new \DateTime("2010-01-30 02:53:22");
        $mongoDate = new \MongoDate(strtotime("2010-01-30 02:53:22"));

        $this->assertEquals($mongoDate, $this->schema->filter('dehydrate', 'createdAt', $date));
        $this->assertEquals($date, $this->schema->filter('hydrate', 'createdAt', $mongoDate));
    }
}

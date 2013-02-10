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
    }

    public function testFieldsExist()
    {
        $this->schema->fields(array(
            'name' => array('type' => 'string'),
            'moneys' => array('type' => 'number'),
            'createdAt' => array('type' => 'date'),
            'friendId' => array('type' => 'id')
        ));

        $this->assertEquals(count($this->schema->fields()), 5);

        $this->assertTrue($this->schema->hasField('_id'));
        $this->assertTrue($this->schema->hasField('name'));
        $this->assertTrue($this->schema->hasField('moneys'));
        $this->assertTrue($this->schema->hasField('createdAt'));
        $this->assertTrue($this->schema->hasField('friendId'));

        $this->assertFalse($this->schema->hasField('apple'));
        $this->assertFalse($this->schema->hasField('banana'));
        $this->assertFalse($this->schema->hasField('strawberry'));
    }

    public function testNonExistantField()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));

        $this->setExpectedException('Exception', "Field 'fish' not found");
        $this->schema->filter('set', 'fish', 'blah');
    }

    public function testNonExistantFilterAction()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));

        $this->setExpectedException('Exception', "Filter 'create' not found");
        $this->schema->filter('create', 'name', '123');
    }

    public function testUnspecifiedFilter()
    {
        $this->schema->fields(array('name' => array('type' => 'random')));

        $date = new \DateTime();
        $this->assertEquals('123', $this->schema->filter('get', 'name', '123'));
        $this->assertEquals(123.456, $this->schema->filter('get', 'name', 123.456));
        $this->assertEquals($date, $this->schema->filter('get', 'name', $date));
    }

    public function testSetIdField()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));

        $mongoId = new \MongoId('51142e3646bd7421ed000000');
        $this->assertEquals($this->schema->filter('set', 'name', '51142e3646bd7421ed000000'), $mongoId);
        $this->assertEquals($this->schema->filter('set', 'name', $mongoId), $mongoId);
    }

    public function testSetStringFilter()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));

        $this->assertEquals($this->schema->filter('set', 'name', 'blah blah blah'), 'blah blah blah');
        $this->assertEquals($this->schema->filter('set', 'name', 123), '123');
    }

    public function testSetIntegerFilter()
    {
        $this->schema->fields(array('count' => array('type' => 'integer')));

        $this->assertEquals($this->schema->filter('set', 'count', 123), 123);
        $this->assertEquals($this->schema->filter('set', 'count', '123'), 123);
        $this->assertEquals($this->schema->filter('set', 'count', 123.27), 123);
        $this->assertEquals($this->schema->filter('set', 'count', 123.89), 123);
    }

    public function testSetFloatFilter()
    {
        $this->schema->fields(array('height' => array('type' => 'float')));

        $this->assertEquals($this->schema->filter('set', 'height', 123), 123);
        $this->assertEquals($this->schema->filter('set', 'height', '123'), 123);
        $this->assertEquals($this->schema->filter('set', 'height', 123.27), 123.27);
        $this->assertEquals($this->schema->filter('set', 'height', 123.89), 123.89);
    }

    public function testSetBooleanFilter()
    {
        $this->schema->fields(array('enabled' => array('type' => 'boolean')));

        $this->assertEquals($this->schema->filter('set', 'enabled', false), false);
        $this->assertEquals($this->schema->filter('set', 'enabled', '123'), true);
        $this->assertEquals($this->schema->filter('set', 'enabled', 1), true);
        $this->assertEquals($this->schema->filter('set', 'enabled', 0), false);
        $this->assertEquals($this->schema->filter('set', 'enabled', null), false);
    }

    public function testSetDateFilter()
    {
        $this->schema->fields(array('enabled' => array('type' => 'date')));

        $date = new \DateTime('2012-11-01');

        $this->assertEquals($this->schema->filter('set', 'enabled', '2012-11-01'), $date);
        $this->assertEquals($this->schema->filter('set', 'enabled', $date), $date);
        $this->assertEquals($this->schema->filter('set', 'enabled', $date->getTimestamp()), $date);
    }

    public function testSetArrayFieldFilter()
    {
        $this->schema->fields(array('cats' => array('type' => array('array', 'string'))));
        $this->schema->fields(array('rates' => array('type' => array('array', 'integer'))));

        $this->assertEquals($this->schema->filter('set', 'cats', array('fluffy', 'tibbles')), array('fluffy', 'tibbles'));
        $this->assertEquals($this->schema->filter('set', 'cats', 'tibbles'), array('tibbles'));
        $this->assertEquals($this->schema->filter('set', 'cats', 1), array('1'));

        $this->assertEquals($this->schema->filter('set', 'rates', array(1, 2, '3')), array(1, 2, 3));
        $this->assertEquals($this->schema->filter('set', 'rates', array('a' => 1, 'b' => 2)), array(1, 2));
    }

    public function testGetIdFieldFilter()
    {
        $this->schema->fields(array('catId' => array('type' => 'id')));
        $this->schema->fields(array('ratIds' => array('type' => array('array', 'id'))));

        $id = '51143ceb46bd74f3ed000002';
        $id2 = '51143ceb46bd74f3ed000002';

        $this->assertEquals(
            $id,
            $this->schema->filter('get', 'catId', new \MongoId($id))
        );

        $this->assertEquals(
            array($id, $id2),
            $this->schema->filter('get', 'ratIds', array(new \MongoId($id), new \MongoId($id2)))
        );
    }

    public function testDateHydrationFilter()
    {
        $this->schema->fields(array('createdAt' => array('type' => 'date')));
        $this->schema->fields(array('loginDates' => array('type' => array('array', 'date'))));

        $date = new \DateTime("2010-01-30 00:00:00");
        $mongoDate = new \MongoDate(strtotime("2010-01-30 00:00:00"));

        $this->assertEquals($mongoDate, $this->schema->filter('dehydrate', 'createdAt', $date));
        $this->assertEquals($date, $this->schema->filter('hydrate', 'createdAt', $mongoDate));
    }
}

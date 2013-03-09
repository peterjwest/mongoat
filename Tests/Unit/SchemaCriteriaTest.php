<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema;

use PHPUnit_Framework_TestCase;

class SchemaCriteriaTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mongoat = new Mongoat();
    }

    public function setUp()
    {
        $this->schema = new Schema($this->mongoat);
    }

    public function testSimpleFields()
    {
        $this->schema->fields(array(
            'name' => array('type' => 'string'),
            'stock' => array('type' => 'integer'),
            'price' => array('type' => 'float'),
            'productId' => array('type' => 'id'),
            'createdAt' => array('type' => 'date')
        ));

        $this->assertEquals(
            array('name' => '3'),
            $this->schema->filterCriteria(array('name' => 3))
        );
        $this->assertEquals(
            array('stock' => 6),
            $this->schema->filterCriteria(array('stock' => 6.5))
        );
        $this->assertEquals(
            array('price' => 36.35),
            $this->schema->filterCriteria(array('price' => '36.35'))
        );
        $this->assertEquals(
            array('productId' => new \MongoId('51143ceb46bd74f3ed000001')),
            $this->schema->filterCriteria(array('productId' => '51143ceb46bd74f3ed000001'))
        );
        $date = new \DateTime();
        $this->assertEquals(
            array('createdAt' => new \MongoDate($date->getTimestamp())),
            $this->schema->filterCriteria(array('createdAt' => $date))
        );
    }

    public function testSimpleFieldsCombined()
    {
        $this->schema->fields(array(
            'name' => array('type' => 'string'),
            'stock' => array('type' => 'integer'),
            'price' => array('type' => 'float'),
            'productId' => array('type' => 'id'),
            'createdAt' => array('type' => 'date')
        ));

        $this->assertEquals(
            array(
                'name' => '3',
                'stock' => 6,
                'price' => 36.35,
                'productId' => new \MongoId('51143ceb46bd74f3ed000001')
            ),
            $this->schema->filterCriteria(array(
                'name' => 3,
                'stock' => 6.5,
                'price' => '36.35',
                'productId' => '51143ceb46bd74f3ed000001'
            ))
        );
    }

    public function testGreaterThanField()
    {
        $this->schema->fields(array('count' => array('type' => 'number')));

        $this->assertEquals(
            array('count' => array('$gt' => 3)),
            $this->schema->filterCriteria(array('count' => array('$gt' => '3')))
        );
    }

    public function testNotGreaterThanField()
    {
        $this->schema->fields(array('count' => array('type' => 'number')));

        $this->assertEquals(
            array('$not' => array('count' => array('$gt' => 3))),
            $this->schema->filterCriteria(array('$not' => array('count' => array('$gt' => '3'))))
        );
    }

    public function testArrayField()
    {
        $this->schema->fields(array('catId' => array('type' => array('array', 'id'))));

        $id = new \MongoId();

        $this->assertEquals(
            array('$set' => array('catId' => array(new \MongoId('51143ceb46bd74f3ed000001'), $id))),
            $this->schema->filterCriteria(array('$set' => array('catId' => array('51143ceb46bd74f3ed000001', $id))))
        );

        $this->assertEquals(
            array('$set' => array('catId' => array())),
            $this->schema->filterCriteria(array('$set' => array('catId' => array())))
        );
    }

    public function testInArrayField()
    {
        $this->schema->fields(array('someId' => array('type' => array('id'))));
        $this->schema->fields(array('count' => array('type' => array('array', 'integer'))));

        $id = new \MongoId();

        $this->assertEquals(
            array('someId' => array('$in' => array($id))),
            $this->schema->filterCriteria(array('someId' => array('$in' => array($id))))
        );

        $this->assertEquals(
            array('count' => array('$in' => array(1, 2, 3))),
            $this->schema->filterCriteria(array('count' => array('$in' => array(1, 2, '3'))))
        );

        $this->assertEquals(
            array('count' => array('$in' => array())),
            $this->schema->filterCriteria(array('count' => array('$in' => array())))
        );

        $this->assertEquals(
            array('count' => array('$in' => array(1))),
            $this->schema->filterCriteria(array('count' => array('$in' => 1)))
        );
    }

    public function testPushPullField()
    {
        $this->schema->fields(array('count' => array('type' => array('array', 'integer'))));

        $this->assertEquals(
            array(
                '$push' => array('count' => 3),
                '$pull' => array('count' => 6),
            ),
            $this->schema->filterCriteria(array(
                '$push' => array('count' => 3),
                '$pull' => array('count' => '6')
            ))
        );
    }

    public function testPushallPullallField()
    {
        $this->schema->fields(array('count' => array('type' => array('array', 'integer'))));

        $this->assertEquals(
            array(
                '$pushAll' => array('count' => array(1, 2, 3)),
                '$pullAll' => array('count' => array(4, 5, 6)),
            ),
            $this->schema->filterCriteria(array(
                '$pushAll' => array('count' => array(1, 2, 3)),
                '$pullAll' => array('count' => array('4', '5', '6'))
            ))
        );
    }

    public function testRenameField()
    {
        $this->schema->fields(array('count' => array('type' => 'integer')));
        $this->schema->fields(array('rates' => array('type' => array('array', 'integer'))));

        $this->assertEquals(
            array('$rename' => array('count' => 'number')),
            $this->schema->filterCriteria(array('$rename' => array('count' => 'number')))
        );

        $this->assertEquals(
            array('$rename' => array('rates' => 'number')),
            $this->schema->filterCriteria(array('$rename' => array('rates' => 'number')))
        );
    }

    public function testFieldSize()
    {
        $this->schema->fields(array('count' => array('type' => array('array', 'integer'))));

        $this->assertEquals(
            array('count' => array('$size' => 3)),
            $this->schema->filterCriteria(array('count' => array('$size' => '3')))
        );
    }

    public function testFieldExists()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));
        $this->schema->fields(array('count' => array('type' => 'integer')));

        $this->assertEquals(
            array('name' => array('$exists' => false), 'count' => array('$exists' => true)),
            $this->schema->filterCriteria(array('name' => array('$exists' => false), 'count' => array('$exists' => 'yep')))
        );
    }

    public function testPopField()
    {
        $this->schema->fields(array('names' => array('type' => array('array', 'string'))));

        $this->assertEquals(
            array('$pop' => array('names' => 3)),
            $this->schema->filterCriteria(array('$pop' => array('names' => '3')))
        );
    }

    public function testRegexField()
    {
        $this->schema->fields(array('name' => array('type' => 'string')));

        $this->assertEquals(
            array('name' => array('$regex' => '.*blah\!?', '$options' => 'i')),
            $this->schema->filterCriteria(array('name' => array('$regex' => '.*blah\!?', '$options' => 'i')))
        );
    }
}

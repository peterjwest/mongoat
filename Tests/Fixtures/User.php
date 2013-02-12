<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;
use WhiteOctober\MongoatBundle\Core\Model;

class User extends Model
{
    public function definition($schema)
    {
        return $schema->fields(array(
            'name' => array('type' => 'string', 'default' => 'Your name'),
            'anything' => array('type' => 'random'),
            'count' => array('type' => 'integer', 'default' => 3),
            'createdAt' => array('type' => 'date'),
            'enabled' => array('type' => 'boolean', 'default' => true),
            'value' => array('type' => 'float'),
            'catId' => array('type' => 'id'),
            'catNames' => array('type' => array('array', 'string'), 'default' => array('Fluffy')),
            'prices' => array('type' => array('array', 'integer')),
            'loginDates' => array('type' => array('array', 'date')),
            'dogIds' => array('type' => array('array', 'id')),
        ));
    }
}

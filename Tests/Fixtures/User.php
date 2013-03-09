<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class User extends Model
{
    public function definition($schema)
    {
        return $schema->fields(array(
            'name' => array('type' => 'string', 'default' => 'Your name'),
            'anything' => array('type' => 'raw'),
            'count' => array('type' => 'integer', 'default' => 3),
            'createdAt' => array('type' => 'date'),
            'loginDates' => array('type' => 'array', 'subtype' => 'date'),
            'enabled' => array('type' => 'boolean', 'default' => true),
            'value' => array('type' => 'float'),
            'catId' => array('type' => 'id'),
            'prices' => array('type' => 'array', 'subtype' => 'integer'),
            'dogIds' => array('type' => 'array', 'subtype' => 'id'),
            'catNames' => array(
                'type' => 'array', 'subtype' => 'string',
                'default' => array('Fluffy', 'Chairman Miaow')
            )
        ));
    }
}

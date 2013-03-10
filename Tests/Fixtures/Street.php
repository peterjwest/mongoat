<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class Street extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'cats' => array('type' => 'hasMany', 'class' => 'Cat', 'inverse' => 'streets')
        ));
    }
}

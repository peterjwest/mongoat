<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class Household extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'cat' => array('type' => 'hasOne', 'class' => 'Cat', 'inverse' => 'households')
        ));
    }
}

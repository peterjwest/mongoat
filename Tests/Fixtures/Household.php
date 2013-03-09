<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class User extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'cats' => array('type' => 'hasMany', 'class' => 'Household', 'inverse' => 'households')
        ));
    }
}

<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class User extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'owner' => array('type' => 'belongsTo', 'class' => 'User', 'inverse' => 'pets'),
            'tail' => array('type' => 'hasOne', 'class' => 'Tail', 'inverse' => 'purchases'),
            'collars' => array('type' => 'hasMany', 'class' => 'Collar', 'inverse' => 'purchases'),
            'households' => array('type' => 'belongsToMany', 'class' => 'Household', 'inverse' => 'purchases')
        ));
    }
}

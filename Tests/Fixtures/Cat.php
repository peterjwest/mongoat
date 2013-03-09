<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class Cat extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'owner' => array('type' => 'belongsTo', 'class' => 'Human', 'inverse' => 'cat'),
            'tail' => array('type' => 'hasOne', 'class' => 'Tail', 'inverse' => 'cat'),
            'collars' => array('type' => 'hasMany', 'class' => 'Collar', 'inverse' => 'cat'),
            'households' => array('type' => 'belongsToMany', 'class' => 'Household', 'inverse' => 'cats')
        ));
    }
}

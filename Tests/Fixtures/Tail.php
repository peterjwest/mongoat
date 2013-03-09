<?php

namespace WhiteOctober\MongoatBundle\Tests\Fixtures;

use WhiteOctober\MongoatBundle\Core\Model;

class Tail extends Model
{
    public function definition($schema)
    {
        return $schema->relationships(array(
            'cat' => array('type' => 'belongsTo', 'class' => 'Cat', 'inverse' => 'tail')
        ));
    }
}

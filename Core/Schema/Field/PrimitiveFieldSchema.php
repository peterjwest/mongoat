<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Field;

use WhiteOctober\MongoatBundle\Core\Model;

class PrimitiveFieldSchema extends FieldSchema
{
    protected $setTypes;

    public function __construct($name, $options, $schema)
    {
        parent::__construct($name, $options, $schema);

        $this->setTypes = array(
            'string' => function($value) { return (string) $value; },
            'integer' => function($value) { return (integer) $value; },
            'boolean' => function($value) { return (boolean) $value; },
            'float' => function($value) { return (float) $value; },
        );
    }

    // Casts to the appropriate primitive field type
    public function set($value)
    {
        return $this->setTypes[$this->options['type']]($value, array(), $this->schema);
    }
}

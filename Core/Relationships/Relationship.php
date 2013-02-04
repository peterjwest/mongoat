<?php

namespace WhiteOctober\MongoatBundle\Core\Relationships;

class Relationship
{
    protected $foreignKey = false;
    protected $multiple = false;
    protected $mongoat;
    protected $name;
    protected $options;

    function __construct($mongoat, $schema, $name, $options)
    {
        $this->mongoat = $mongoat;
        $this->name = $name;

        if (!isset($options['inverse'])) $options['inverse'] = false;

        // Generates the foreign key field name for inverse relationships
        if (!$this->foreignKey && isset($options['inverse'])) {
            $options['fieldName'] = $options['inverse'].'Id';
        }

        // Generates default foreign key field name
        if (!isset($options['fieldName'])) {
            $options['fieldName'] = $name.'Id';
        }

        // Sets a field for the foreign key, if applicable
        if ($this->foreignKey) {
            $type = $this->multiple ? array('array', 'id') : 'id';
            $schema->fields(array($options['fieldName'] => array('type' => $type)));
        }

        $this->options = $options;
    }
}

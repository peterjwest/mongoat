<?php

namespace WhiteOctober\MongoatBundle\Core;

class RelationshipSchema
{
    static $relationshipTypes = array(
        'hasOne' => array('foreignKey' => false, 'multiple' => false),
        'hasMany' => array('foreignKey' => false, 'multiple' => true),
        'belongsTo' => array('foreignKey' => true, 'multiple' => false),
        'belongsToMany' => array('foreignKey' => true, 'multiple' => true)
    );

    protected $foreignClass;
    protected $foreignKey = false;
    protected $multiple = false;
    protected $mongoat;
    protected $name;
    protected $inverse;
    protected $fieldName;

    function __construct($mongoat, $name, $options, $schema)
    {
        $this->mongoat = $mongoat;

        if (!isset(static::$relationshipTypes[$options['type']])) {
            throw new \Exception("$name relationship type does not exist in ".get_class($this));
        }

        $type = static::$relationshipTypes[$options['type']];
        $this->foreignKey = $type['foreignKey'];
        $this->multiple = $type['multiple'];

        $this->name = $name;
        $this->foreignClass = $options['class'];
        $this->inverse = isset($options['inverse']) ? $options['inverse'] : false;

        // Generates the field name for this relationship
        $this->fieldName = (!$this->foreignKey && $this->inverse) ? $this->inverse.'Id' : $name.'Id';

        // Creates a field for the foreign key, if one exists
        if ($this->foreignKey) {
            $type = $this->multiple ? array('array', 'id') : 'id';
            $schema->fields(array($this->fieldName => array('type' => $type)));
        }
    }

    function multiple()
    {
        return $this->multiple;
    }

    function foreignClass()
    {
        return $this->foreignClass;
    }

    function fieldName()
    {
        return $this->fieldName;
    }

    function foreignKey()
    {
        return $this->foreignKey;
    }

    function inverse()
    {
        return $this->inverse;
    }
}

<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Relationship;

class RelationshipSchema
{
    static $relationshipTypes = array(
        'hasOne' => array('foreignKey' => false, 'multiple' => false),
        'hasMany' => array('foreignKey' => false, 'multiple' => true),
        'belongsTo' => array('foreignKey' => true, 'multiple' => false),
        'belongsToMany' => array('foreignKey' => true, 'multiple' => true)
    );

    protected $name;
    protected $foreignClass;
    protected $foreignKey = false;
    protected $multiple = false;
    protected $inverse;
    protected $fieldName;

    function __construct($name, $options, $schema)
    {
        if (!isset($options['type'])) {
            throw new \Exception("Relationship options must include 'type' in ".get_class($this));
        }

        $typeName = $options['type'];

        if (!isset(static::$relationshipTypes[$typeName])) {
            throw new \Exception("$type relationship type does not exist in ".get_class($this));
        }

        $type = static::$relationshipTypes[$typeName];

        if (!$type['foreignKey'] && !isset($options['inverse'])) {
            throw new \Exception("$type relationship options must include 'inverse' in ".get_class($this));
        }

        $this->foreignKey = $type['foreignKey'];
        $this->multiple = $type['multiple'];

        $this->name = $name;
        $this->foreignClass = $options['class'];
        $this->inverse = isset($options['inverse']) ? $options['inverse'] : false;

        // Generates the field name for this relationship
        $this->fieldName = (!$this->foreignKey && $this->inverse) ? $this->inverse.'Id' : $name.'Id';

        // Creates a field for the foreign key, if one exists
        if ($this->foreignKey) {
            $options = $this->multiple ? array('type' => 'array', 'subtype' => 'id') : array('type' => 'id');
            $schema->fields(array($this->fieldName => $options));
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

<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Field;

use WhiteOctober\MongoatBundle\Core\Model;

class ArrayFieldSchema extends FieldSchema
{
    protected $subtypeSchema;
    protected $requiredOptions = array('type', 'subtype');

    public function __construct($name, $options, $schema)
    {
        parent::__construct($name, $options, $schema);

        // Creates a schema for the array items
        $this->subtypeSchema = $this->schema->fieldSchema($name, array('type' => $this->options['subtype']));
    }

    public function get($value)
    {
        return $this->filter('get', $value);
    }

    public function set($value)
    {
        return $this->filter('set', $value);
    }

    public function hydrate($value)
    {
        return $this->filter('hydrate', $value);
    }

    public function dehydrate($value)
    {
        return $this->filter('dehydrate', $value);
    }

    public function filter($method, $value)
    {
        // Null values are converted to an empty array
        if ($value === null) return array();

        // Wraps non-array values in an array
        $array = is_array($value) ? $value : array($value);

        // Filter each item in the array according to the subtype schema
        $newArray = array();
        foreach ($array as $value) {
            $newArray[] = $this->subtypeSchema->$method($value);
        }
        return $newArray;
    }

    public function defaultValue()
    {
        return isset($this->options['default']) ? $this->options['default'] : array();
    }
}

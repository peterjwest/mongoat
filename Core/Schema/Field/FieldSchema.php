<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Field;

class FieldSchema
{
    protected $name;
    protected $options;
    protected $requiredOptions = array('type');
    protected $schema;

    public function __construct($name, $options, $schema)
    {
        foreach($this->requiredOptions as $option) {
            if (!isset($options[$option]) || !$options[$option]) {
                throw new \Exception(
                    "Field options must include '$option' in ".get_class($this)
                );
            }
        }

        $this->name = $name;
        $this->options = $options;
        $this->schema = $schema;
    }

    public function defaultValue()
    {
        return isset($this->options['default']) ? $this->options['default'] : null;
    }

    public function name()
    {
        return $this->options['name'];
    }

    public function type()
    {
        return $this->options['type'];
    }

    public function subtype()
    {
        if (isset($this->options['subtype']) && $this->options['subtype']) {
            return $this->options['subtype'];
        }
        return null;
    }

    public function set($value)
    {
        return $value;
    }

    public function get($value)
    {
        return $value;
    }

    public function hydrate($value)
    {
        return $value;
    }

    public function dehydrate($value)
    {
        return $value;
    }
}

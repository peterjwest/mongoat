<?php

namespace WhiteOctober\MongoatBundle\Core\Schema;

class Schema
{
	static $relationshipSchemas = array(
		'WhiteOctober\MongoatBundle\Core\Schema\RelationshipSchema'
	);

	static $fieldSchemas = array(
		'raw' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\FieldSchema',
		'id' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\IdFieldSchema',
		'string' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\PrimitiveFieldSchema',
		'integer' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\PrimitiveFieldSchema',
		'float' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\PrimitiveFieldSchema',
		'boolean' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\PrimitiveFieldSchema',
		'date' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\DateFieldSchema',
		'array' => 'WhiteOctober\MongoatBundle\Core\Schema\Field\ArrayFieldSchema'
	);

	protected $mongoat;
	protected $fields = array();
	protected $operators = array();
	protected $relationships = array();

	public function __construct($mongoat)
	{
		$this->mongoat = $mongoat;
		$this->operators();
		$this->fields(array('_id' => array('type' => 'id')));
	}

	// Creates special rules for types in mongo operators
	protected function operators()
	{
		$this->operators = array(
			'parent' => array(
				'arrayOfItems' => array(
					'operators' => array('$push' => true, '$pull' => true, '$addToSet' => true),
					'filter' => function($type, $subtype) { return array($subtype, null); }
				),
				'index' => array(
					'operators' => array('$pop' => true),
					'filter' => function($type, $subtype) { return array('integer', null); }
				),
				'fieldName' => array(
					'operators' => array('$rename' => true),
					'filter' => function($type, $subtype) { return array('string', null); }
				)
			),

			'current' => array(
				'arrayElement' => array(
					'operators' => array('$all' => true, '$in' => true, '$nin' => true),
					'filter' => function($type, $subtype) {
						return $subtype ? array($type, $subtype) : array('array', $type);
					}
				),
				'integer' => array(
					'operators' => array('$size' => true, '$type' => true),
					'filter' => function($type, $subtype) { return array('integer', null); }
				),
				'boolean' => array(
					'operators' => array('$exists' => true),
					'filter' => function($type, $subtype) { return array('boolean', null); }
				),
				'string' => array(
					'operators' => array('$regex' => true, '$options' => true),
					'filter' => function($type, $subtype) { return array('string', null); }
				)
			)
		);
	}

	// Gets all fields or adds fields
	public function fields($fields = null)
	{
		if (func_num_args() == 0) return $this->fields;

		foreach ($fields as $name => $options) {
			$this->fields[$name] = $this->fieldSchema($name, $options);
		}

		return $this;
	}

	// Creates a schema for a field
	public function fieldSchema($name, $options)
	{
		if (!isset($options['type']) || !isset(static::$fieldSchemas[$options['type']])) {
			throw new \BadMethodCallException("Field options must include 'type' in ".get_class($this));
		}

		if (!$this->mongoat) throw new \Exception('hi');

		$class = $this->mongoat->fullClass(static::$fieldSchemas[$options['type']]);

		return new $class($name, $options, $this);
	}

	// Gets a field by name
	public function field($name)
	{
		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}

	// Gets all relationships or adds relationships
	public function relationships($relationships = null)
	{
		if (func_num_args() == 0) {
			return $this->relationships;
		}
		foreach ($relationships as $name => $options) {
			$class = $this->mongoat->fullClass(static::$relationshipSchemas[0]);
			$this->relationships[$name] = new $class($this->mongoat, $name, $options, $this);
		}
	}

	// Gets a relationship by name
	public function relationship($name)
	{
		return isset($this->relationships[$name]) ? $this->relationships[$name] : null;
	}

	// Clears all fields and relationships
	public function clear()
	{
		$this->fields = array();
		$this->fields(array('_id' => array('type' => 'id')));

		$this->relationships = array();

		return $this;
	}

	// Deep filter of data passed to Mongo
    public function filterCriteria($data, $parentField = null, $parent = null)
    {
        $filtered = array();
        foreach ($data as $current => $value) {
        	$field = $parentField;

        	// Sets the scope to the earliest field found
            if (!$this->isOperator($current) && !$field) $field = $current;

            // Recursively filters criteria if a field is not yet specified,
            // or if the value array contains more operators
            if (is_array($value) && (!$field || !$this->valueArray($value))) {
            	$filtered[$current] = $this->filterCriteria($value, $field, $current);
            }

            // Otherwise filters the field
            else if ($this->field($field)) {

            	// Gets standard types for the field
            	$type = $this->field($field)->type();
				$subtype = $this->field($field)->subtype();

				// TODO: Add errors here when fields are incompatible / non-existant

				// Applies custom data types for specific parent operators
				foreach($this->operators['parent'] as $group) {
					if (isset($group['operators'][$parent])) {
						list($type, $subtype) = $group['filter']($type, $subtype);
					}
				}

				// Applies custom data types for specific operators
				foreach($this->operators['current'] as $group) {
					if (isset($group['operators'][$current])) {
						list($type, $subtype) = $group['filter']($type, $subtype);
					}
				}

				// Filter fields through appropriate schemas
				// TODO: make this more efficent
				$options = array('type' => $type, 'subtype' => $subtype);
				$field = $this->fieldSchema(null, $options);
                $filtered[$current] = $field->dehydrate($field->set($value));
            }
        }
        return $filtered;
    }

	// Determines if an array contains no further arrays or operators
    protected function valueArray($array)
    {
    	foreach($array as $key => $item) {
    		if (is_array($item)) return false;
    		if ($this->isOperator($key)) return false;
    	}
    	return true;
    }

    // Determines if a key is a mongo operator
    protected function isOperator($key)
    {
    	return preg_match('/^\$[a-z]+$/i', $key);
    }
}

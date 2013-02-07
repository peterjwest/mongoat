<?php

namespace WhiteOctober\MongoatBundle\Core;

class Schema
{
	static $relationshipSchemaClass = 'WhiteOctober\MongoatBundle\Core\RelationshipSchema';
	static $operatorTypes = array(
		'polymorphicArray' => array('$push' => true, '$pull' => true, '$addToSet' => true),
		'arrayElement' => array('$all' => true, '$in' => true, '$nin' => true),
		'fieldName' => array('$rename' => true),
		'integer' => array('$size' => true, '$type' => true),
		'boolean' => array('$exists' => true),
		'index' => array('$pop' => true),
		'string' => array('$regex' => true, '$options' => true)
	);

	protected $mongoat;
	protected $fields = array('_id' => array('type' => 'id'));
	public $filters = array();
	public $relationships = array();

	public function __construct($mongoat)
	{
		$this->mongoat = $mongoat;
		$this->filters();
	}

	// Creates filters for fields
	protected function filters()
	{
		$that = $this;
		$this->filters = array(
			// These functions filter data set on the model
			'set' => array(
				'id' => function($id) {
					if ($id instanceof \MongoId) return $id;
					if ($id instanceof Model) return $id->mongoId();
					if ($id === null) return null;
					return new \MongoId($id);
				},
				'string' => function($value) {
					return (string) $value;
				},
				'integer' => function($value) {
					return (integer) $value;
				},
				'float' => function($value) {
					return (float) $value;
				},
				'boolean' => function($value) {
					return !!$value;
				},
				'date' => function($value) {
					if (is_string($value)) return new \DateTime($value);
					if (is_int($value)) {
						$date = new \DateTime();
						$date->setTimestamp($value);
						return $date;
					}
					return $value;
				}
			),

			// These functions filter data which is requested from the model
			'get' => array(
				'id' => function($id) {
					return (string) $id;
				}
			),

			// These functions filter data saved to the database
			'dehydrate' => array(
				'id' => function($id) { return $id; },
				'date' => function($value) {
					return $value instanceof \DateTime ? new \MongoDate($value->getTimestamp()) : null;
				}
			),

			// These function filter data queried from the database
			'hydrate' => array(
				'id' => function($id) { return $id; },
				'date' => function($value) {
					if ($value === null) return null;
					$date = new \DateTime();
					$date->setTimestamp($value->sec);
					return $date;
				}
			)
		);

		// Meta filter for array fields
		foreach($this->filters as $action => $filters) {
			$this->filters[$action]['array'] = function($type, $value) use ($that, $action) {
				$array = is_array($value) ? $value : array($value);
				return array_map($that->filters[$action][$type], array_values($array));
			};
		}
	}

	// Getter / setter for fields
	// Gets all fields or adds one or more fields
	public function fields($fields = null)
	{
		if (func_num_args() == 0) {
			return $this->fields;
		}
		foreach ($fields as $name => $options) {
			if (is_string($options)) $this->fields[$options] = array();
			else $this->fields[$name] = $options;
		}
		return $this;
	}

	// Clears all fields and relationships
	public function clear()
	{
		$this->fields = array('_id' => array('type' => 'id'));
		$this->relationships = array();
		return $this;
	}

	// Adds one or more relationship definitions
	public function relationships($relationships)
	{
		foreach ($relationships as $name => $options) {
			$class = $this->mongoat->fullClass(static::$relationshipSchemaClass);
			$this->relationships[$name] = new $class($this->mongoat, $name, $options, $this);
		}
	}

	public function hasField($field)
	{
		if (isset($this->fields[$field])) return true;
		return false;
	}

	// Validates a field against its schema, returns an array of errors
	public function validateField($field, $value)
	{
		// TODO
	}

	// Gets the data types for a field
	protected function fieldType($field)
	{
		$type = isset($this->fields[$field]) ? $this->fields[$field]['type'] : null;
		return is_array($type) ? $type[0] : $type;
	}

	protected function fieldSubtype($field)
	{
		$type = $this->fields[$field]['type'];
		return is_array($type) ? $type[1] : null;
	}

	// Filters a field value through a certain action
	public function filter($action, $field, $value)
	{
		$type = $this->fieldType($field);
		$subtype = $this->fieldSubtype($field);

		return $this->filterType($action, $type, $subtype, $value);
	}

	// Filters a value by its type through a certain action
	public function filterType($action, $type, $subtype, $value)
	{
		// Finds appropriate filter
		$filters = $this->filters[$action];

		// If there's no filter, return the value unaltered;
		if (!isset($filters[$type])) return $value;

		// Call with subtype, if it exists
		return $subtype ? $filters[$type]($subtype, $value) : $filters[$type]($value);
	}

	// Deep filter of data passed to Mongo
    public function filterCriteria($data, $field = null, $parent = null)
    {
        $filtered = array();
        foreach ($data as $key => $value) {
            if (!preg_match('/^\$[a-z]+$/i', $key) && !is_numeric($key)) $field = $key;

            if (is_array($value) && ($this->fieldType($field) != 'array' || !$this->flatArray($value))) {
            	$filtered[$key] = $this->filterCriteria($value, $field, $key);
            }

            else if ($field !== null) {

            	// Gets default types for the field
            	$type = $this->fieldType($field);
				$subtype = $this->fieldSubtype($field);

				// These operators make the field within take a single value
				if (isset(static::$operatorTypes['polymorphicArray'][$parent])) {
					$type = $subtype;
					$subtype = null;
				}

				// These operators make the field within take an array of values
				if (isset(static::$operatorTypes['arrayElement'][$key])) {
					$subtype = $type;
					$type = 'array';
				}

				// These operators make the field within take an integer value
				if (isset(static::$operatorTypes['index'][$parent])) {
					$type = 'integer';
					$subtype = null;
				}

				// These operators make the field within take a string value
				if (isset(static::$operatorTypes['fieldName'][$parent])) {
					$type = 'string';
					$subtype = null;
				}

				// These operators take integer values
				if (isset(static::$operatorTypes['integer'][$key])) {
					$type = 'integer';
					$subtype = null;
				}

				// These operators take boolean values
				if (isset(static::$operatorTypes['boolean'][$key])) {
					$type = 'boolean';
					$subtype = null;
				}

				// These operators take string values
				if (isset(static::$operatorTypes['string'][$key])) {
					$type = 'string';
					$subtype = null;
				}

				// Runs filters on the fields
				$value = $this->filterType('set', $type, $subtype, $value);
				$value = $this->filterType('dehydrate', $type, $subtype, $value);

                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

	// Gets a relationship
	public function relationship($name) {
		return isset($this->relationships[$name]) ? $this->relationships[$name] : null;
	}

	// Determines if an array is flat i.e. contains no arrays
    protected function flatArray($array)
    {
    	foreach($array as $item) if (is_array($item)) return false;
    	return true;
    }
}

<?php

namespace WhiteOctober\MongoatBundle\Core;

class Schema
{
	static $relationshipSchemaClass = 'WhiteOctober\MongoatBundle\Core\RelationshipSchema';

	protected $mongoat;
	protected $fields = array('_id' => array('type' => 'id'));
	protected $operators = array();
	protected $relationships = array();
	public $filters = array();

	public function __construct($mongoat)
	{
		$this->mongoat = $mongoat;
		$this->filters();
		$this->operators();
	}

	// Creates filters for fields
	protected function filters()
	{
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
				'date' => function($value) {
					return $value instanceof \DateTime ? new \MongoDate($value->getTimestamp()) : null;
				}
			),

			// These function filter data queried from the database
			'hydrate' => array(
				'date' => function($value) {
					if ($value === null) return null;
					$date = new \DateTime();
					$date->setTimestamp($value->sec);
					return $date;
				}
			)
		);

		// Meta filter for array fields
		$that = $this;
		foreach($this->filters as $action => $filters) {
			$this->filters[$action]['array'] = function($type, $value) use ($that, $action) {

				// Null values are converted to an empty array
				if ($value === null) return array();

				// Wraps non-array values in an array
				$array = is_array($value) ? $value : array($value);

				// Runs each item through a filter if one exists
				if (!isset($that->filters[$action][$type])) return $array;
				return array_map($that->filters[$action][$type], array_values($array));
			};
		}
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
		if (func_num_args() == 0) {
			return $this->fields;
		}
		foreach ($fields as $name => $options) {
			if (is_string($options)) $this->fields[$options] = array();
			else $this->fields[$name] = $options;
		}
		return $this;
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
			$class = $this->mongoat->fullClass(static::$relationshipSchemaClass);
			$this->relationships[$name] = new $class($this->mongoat, $name, $options, $this);
		}
	}

	// Gets a relationship by name
	public function relationship($name)
	{
		return isset($this->relationships[$name]) ? $this->relationships[$name] : null;
	}

	// Gets the default value for a field
	public function defaultValue($name)
	{
		$value = null;
		if (isset($this->fields[$name]) && isset($this->fields[$name]['default'])) {
			$value = $this->fields[$name]['default'];
		}
		return $this->filter('set', $name, $value);
	}

	// Clears all fields and relationships
	public function clear()
	{
		$this->fields = array('_id' => array('type' => 'id'));
		$this->relationships = array();
		return $this;
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
		return is_array($type) && isset($type[1]) ? $type[1] : null;
	}

	// Filters a field value through a certain action
	public function filter($action, $field, $value)
	{
		if (!isset($this->fields[$field])) throw new \Exception("Field '$field' not found");

		$type = $this->fieldType($field);
		$subtype = $this->fieldSubtype($field);

		return $this->filterType($action, $type, $subtype, $value);
	}

	// Filters a value by its type through a certain action
	protected function filterType($action, $type, $subtype, $value)
	{
		// Finds appropriate filter or throws an error
		if (!isset($this->filters[$action])) throw new \Exception("Filter '$action' not found");
		$filters = $this->filters[$action];

		// If there's no filter, return the value unaltered;
		if (!isset($filters[$type])) return $value;

		// Call with subtype, if it exists
		return $subtype ? $filters[$type]($subtype, $value) : $filters[$type]($value);
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
            else if ($field) {

            	// Gets standard types for the field
            	$type = $this->fieldType($field);
				$subtype = $this->fieldSubtype($field);

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

				// Runs filters on the fields
				$value = $this->filterType('set', $type, $subtype, $value);
				$value = $this->filterType('dehydrate', $type, $subtype, $value);

                $filtered[$current] = $value;
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

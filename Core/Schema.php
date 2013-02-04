<?php

namespace WhiteOctober\MongoatBundle\Core;

class Schema
{
	protected $mongoat;
	protected $fields = array('_id' => array('type' => 'id'));
	public $filters = array();
	public $relationships = array();
	public $relationshipTypes = array(
		'hasOne' => 'WhiteOctober\MongoatBundle\Core\Relationships\HasOne',
		'hasMany' => 'WhiteOctober\MongoatBundle\Core\Relationships\HasMany',
		'belongsTo' => 'WhiteOctober\MongoatBundle\Core\Relationships\BelongsTo',
		'belongsToMany' => 'WhiteOctober\MongoatBundle\Core\Relationships\BelongsToMany'
	);

	public function __construct($mongoat)
	{
		$this->mongoat = $mongoat;
		$that = $this;
		$this->filters = array(
			// These functions filter data set on the model
			'set' => array(
				'id' => function($id) {
					if ($id instanceof \Mongoid) return $id;
					if ($id instanceof Model) return $id->mongoId();
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
					return is_string($value) || is_numeric($value) ? new \DateTime($value) : $value;
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
				return array_map($that->filters[$action][$type], $array);
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
			$class = $this->mongoat->fullClass($this->relationshipTypes[$options['type']]);
			$this->relationships[$name] = new $class($this->mongoat, $this, $name, $options);
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
	public function fieldType($field)
	{
		$type = isset($this->fields[$field]) ? $this->fields[$field]['type'] : null;
		return is_array($type) ? $type[0] : $type;
	}

	public function fieldSubtype($field)
	{
		$type = $this->fields[$field]['type'];
		return is_array($type) ? $type[1] : null;
	}

	// Filters a field based on its type
	public function filter($action, $field, $value)
	{
		$fieldType = $this->fieldType($field);
		$fieldSubtype = $this->fieldSubtype($field);
		$filters = $this->filters[$action];

		// Find type
		$type = isset($filters[$fieldType]) ? $filters[$fieldType] : null;

		if (!$type) return $value;

		// Call with subtype, if it exists
		return $fieldSubtype ? $type($fieldSubtype, $value) : $type($value);
	}

	// Deep filter of data passed to Mongo
    public function filterCriteria($data, $field = null)
    {
        $filtered = array();
        foreach ($data as $key => $value) {
            if (!preg_match('/^\$[a-z]+$/i', $key) && !is_numeric($key)) $field = $key;

            if (is_array($value) && ($this->fieldType($field) != 'array' || !$this->flatArray($value))) {
            	$filtered[$key] = $this->filterCriteria($value, $field);
            }

            else if ($field !== null) {
                $value = $this->filter('set', $field, $value);
                $value = $this->filter('dehydrate', $field, $value);
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

<?php

namespace WhiteOctober\MongoatBundle\Core;

class Schema
{
	static protected $filters = {
		'set' => array(
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
			}
			'array' => function($value) {
				return is_array($value) ? $value : array($value);
			}
				'date' => function($value) {
				return is_string($value) || is_numeric($value) ? new \DateTime($value) : $value;
			}
		),

		'insert' => array(
			'date' => function($value) {
				return $value instanceof \DateTime ? new \MongoDate($value->getTimestamp()) : null;
			}
		),

		'hydrate' => array(
			'date' => function($value) {
				$date = new \DateTime();
				$date->setTimestamp($value->sec);
				return $date;
			}
		)
	};

	protected $fields = array();
	protected $relationships = array();

	// Adds one or more fields
	public function fields($fields)
	{
		// Merge $fields into $this->fields;
	}

	// Adds one or more relationships
	public function relationships($references)
	{
		// Merge $relationships into $this->relationships;
	}

	public function hasField($field)
	{
		return true;
	}

	// Validates the object against its schema, returns an array of errors
	public function validateField($field, $value)
	{
		// Validate the field here
		return array();
	}

	// Filters a field based on its type
	public function filter($event, $field, $value)
	{
		$fieldType = $this->fieldType($field);
		$filters = self::$filters[$event];
		return isset($filters[$fieldType]) ? $filters[$fieldType]($value) : $value;
	}
}

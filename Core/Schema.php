<?php

namespace WhiteOctober\MongoatBundle\Core;

class Schema
{
	protected $filters = array();
	protected $fields = array('_id' => array('type' => 'id'));
	public $relationships = array();

	public function __construct()
	{
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
				'array' => function($value) {
					return is_array($value) ? $value : array($value);
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

		$this->relationshipFilters = array(
			'belongsTo' => array(
				'field' => 'id',
				'get' => function($mongoat, $document, $options) {
					return $mongoat
						->find($options['class'])
						->where('_id', $document->get($options['fieldName']))
						->one();
				},
				'set' => function($mongoat, $document, $options, $relation) {
					return $document->set($options['fieldName'], $relation);
				}
			),
			'hasMany' => array(
				'field' => null,
				'get' => function($mongoat, $document, $options) {
					return $mongoat
						->find($options['class'])
						->where($options['fieldName'], $document)
						->all();
				},
				'set' => function($mongoat, $document, $options, $relations) {
					$mongoat->update($options['class'])
						->where($options['fieldName'], $document)
						->changes(array('$set' => array($options['fieldName'] => null)))
						->all();

					$mongoat->update($options['class'])
						->where('_id', array('$in' => $relations))
						->changes(array('$set' => array($options['fieldName'] => $document)))
						->all();

					// Set relations on model
					//$mongo->cache($document, $relation)->set($relations);
					//$relations->set($inverseField, $document);

					return $document;
				}
			)
		);
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

	// Adds one or more relationships
	public function relationships($relationships)
	{
		foreach ($relationships as $name => $options) {
			if (is_string($options)) {
				$name = $options;
				$options = array('type' => 'string');
			}

			if ($this->relationshipFilters[$options['type']]['field'] == 'id') {
				if (!isset($options['fieldName'])) {
					$options['fieldName'] = $name.'Id';
				}
				$this->fields[$options['fieldName']] = array('type' => 'id');
			}

			$this->relationships[$name] = $options;
		}
	}

	public function hasField($field)
	{
		if (isset($this->fields[$field])) return true;
		return false;
	}

	public function hasRelationship($field)
	{
		return isset($this->relationships[$field]);
	}

	// Validates a field against its schema, returns an array of errors
	public function validateField($field, $value)
	{
		// TODO
	}

	// Gets the data type for a field
	public function fieldType($field)
	{
		return $this->fields[$field]['type'];
	}

	// Filters a field based on its type
	public function filter($action, $field, $value)
	{
		$fieldType = $this->fieldType($field);
		$filters = $this->filters[$action];
		return isset($filters[$fieldType]) ? $filters[$fieldType]($value) : $value;
	}

	// Deep filter of data passed to Mongo
    public function filterCriteria($data, $field = null)
    {
        $filtered = array();
        foreach ($data as $key => $value) {
            if (!preg_match('/^\$[a-z]+$/i', $key) && !is_numeric($key)) $field = $key;

            if (is_array($value)) $filtered[$key] = $this->filterCriteria($value, $field);

            else if ($field !== null) {
                $value = $this->filter('set', $field, $value);
                $value = $this->filter('dehydrate', $field, $value);
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

	// Finds a relationship (no caching yet)
	public function relationship($mongoat, $document, $name, $relation = null) {
		$options = $this->relationships[$name];
		$filters = $this->relationshipFilters[$options['type']];
		return $filters[func_num_args() == 3 ? 'get' : 'set']($mongoat, $document, $options, $relation);
	}
}

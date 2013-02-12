<?php

namespace WhiteOctober\MongoatBundle\Core;

class Query
{
	protected $mongoat;
	protected $class;
	protected $schema;
	protected $criteria = array();
	protected $changes = array();
	protected $relations = array();
	protected $type = "find";
	protected $limit = null;
	protected $skip = null;
	protected $page = null;
	protected $fields = null;
	protected $options = array('safe' => true);

    // Adds reference to Mongoat repository manager
	public function __construct($mongoat, $class)
	{
		$this->mongoat = $mongoat;
		$this->class = $class;
	}

	// Getter / setter for Schema instance
    public function schema($schema = null)
    {
        if (func_num_args() == 0) {
            if ($this->schema === null) {
	            $this->schema = $this->mongoat->create($this->class)->schema();
            }
            return $this->schema;
        }
        $this->schema = $schema;
        return $this;
    }

	// Adds one or more criteria to the query
	public function where($criteria, $criterion = null)
	{
		if (func_num_args() > 1) {
			$criteria = array((string) $criteria => $criterion);
		}
		// Merge criteria intelligently (not like this)
		$this->criteria = array_merge($this->criteria, $criteria);

		return $this;
	}

	// Runs the query, returning an array of documents up to size $this->limit
	public function all()
	{
		if ($this->type == 'find') {

			// Sets criteria and required fields for the query
			if (is_array($this->fields)) $cursor = $this->collection()->find(
				$this->schema()->filterCriteria($this->criteria),
				$this->fields
			);
			else $cursor = $this->collection()->find($this->schema()->filterCriteria($this->criteria));

			// Applies limits and results to skip
			if ($this->limit !== null) $cursor->limit($this->limit);
			if ($this->skip !== null) $cursor->skip($this->skip);
			else if ($this->page !== null) $cursor->limit($this->limit * ($this->page - 1));

			// Instantiates and hydrates models
			$models = array();
			foreach(iterator_to_array($cursor) as $item) {
				$models[] = $this->mongoat->create($this->class)->hydrate($item)->unsaved(false);
			}
			return $models;
		}

		if ($this->type == 'update') {

			$this->options['multiple'] = true;
			return $this->collection()->update(
				$this->schema()->filterCriteria($this->criteria),
				$this->schema()->filterCriteria($this->changes),
				$this->options
			);

		}

		if ($this->type == 'delete') {

			$this->options['justOne'] = false;
			return $this->collection()->remove(
				$this->schema()->filterCriteria($this->criteria),
				$this->options
			);
		}
	}

	// Runs the query with a limit of 1, returning one document
	public function one()
	{
		if ($this->type == 'find') {
			// Sets criteria and required fields for the query
			if (is_array($this->fields)) $data = $this->collection()->findOne(
				$this->schema()->filterCriteria($this->criteria),
				$this->fields
			);
			else $data = $this->collection()->findOne($this->schema()->filterCriteria($this->criteria));

			// Instantiates and hydrates models
			return $data === null ? null : $this->mongoat->create($this->class)->hydrate($data)->unsaved(false);
		}

		if ($this->type == 'update') {
			$this->options['multiple'] = false;
			return $this->collection()->update(
				$this->schema()->filterCriteria($this->criteria),
				$this->schema()->filterCriteria($this->changes),
				$this->options
			);
		}

		if ($this->type == 'delete') {
			$this->options['justOne'] = true;
			return $this->collection()->remove(
				$this->schema()->filterCriteria($this->criteria),
				$this->options
			);
		}
	}

	// Runs the query as a count query
	public function count()
	{
		return $this->collection()->count($this->schema()->filterCriteria($this->criteria));
	}

	// Sets which fields to return.
	// - Setting this to an array returns the specified fields,
	// - Setting to false returns only the id
	// - Setting to null returns all fields
	public function fields($fields)
	{
		if ($fields === false) $fields = array();
		$this->fields = $fields;
		return $this;
	}

	// Sets relationship subqueries to be run and inserted into the objects
	public function populate($relations)
	{
		// TODO - make this actually do something
		$this->relations = array_merge($this->relations, is_array($relations) ? $relations : array($relations));
		return $this;
	}

	// Sets the query type
	public function type($type)
	{
		$this->type = $type;
		return $this;
	}

	// Sets a limit
	public function limit($number)
	{
		$this->limit = $this->boundedInt($number, 0);
		return $this;
	}

	// Sets a number of results to skip
	public function skip($number)
	{
		$this->skip = $this->boundedInt($number, 0);
		$this->page = null;
		return $this;
	}

	// Simple method to get results by page
	public function page($page)
	{
		$this->page = max((int)$page, 1);
		$this->skip = null;
		return $this;
	}

	public function changes($changes)
	{
		$this->changes = $changes;
		return $this;
	}

	// Setter to toggle an upsert query
	public function upsert($upsert)
	{
		// TODO
	}

	// Clones the query
	public function copy()
	{
		// TODO
	}

	// Gets the collection from the current Mongo database/connection
	protected function collection()
	{
		return $this->mongoat->collection($this->class);
	}
}

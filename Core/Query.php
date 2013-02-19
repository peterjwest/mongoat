<?php

namespace WhiteOctober\MongoatBundle\Core;

class Query
{
	protected $mongoat;
	protected $class;
	protected $schema;
	protected $criteria = array();
	protected $changes = array();
	protected $populates = array();
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

			// Instantiates and hydrates documents
			$documents = array();
			foreach(iterator_to_array($cursor) as $item) {
				$documents[] = $this->mongoat->create($this->class)->hydrate($item)->unsaved(false);
			}

			// Run populates
			foreach($this->populates as $name) {
				$criteria = array();
				foreach($documents as $document) {
					$query = $document->relationship($name)->find();
					$rawCriteria = $document->relationship($name)->criteria();
					$criteria = $this->merge($criteria, $query->schema()->filterCriteria($rawCriteria));
				}
				$items = $this->hash($query->where($criteria)->all(), 'id');
				foreach($documents as $document) {
					$document->relationship($name)->populate($items);
				}
			}

			return $documents;
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
	public function populate($populates)
	{
		$this->populates = array_merge($this->populates, is_array($populates) ? $populates : array($populates));
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

	// Deeply merges two arrays
	protected function merge($first, $second)
	{
		foreach($second as $key => $item) {
			if (isset($first[$key]) && is_array($first[$key]) && is_array($item)) {
				if ($this->numericArray($first[$key]) && $this->numericArray($item)) {
					$first[$key] = array_merge($first[$key], $item);
				}
				else $first[$key] = $this->merge($first[$key], $item);
			}
			else $first[$key] = $item;
		}
		return $first;
	}

	// Determines if an array contains no further arrays
    protected function valueArray($array)
    {
    	foreach($array as $key => $item) {
    		if (is_array($item)) return false;
    	}
    	return true;
    }

    // Determines if an array is numerically indexed
    protected function numericArray($array)
    {
		return count(array_filter(array_keys($array), 'is_int')) === count($array);
	}

	protected function hash($array, $key)
	{
		$hash = array();
		foreach($array as $item) {
			$hash[$item->$key()] = $item;
		}
		return $hash;
	}
}

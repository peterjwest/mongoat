<?php

namespace WhiteOctober\MongoatBundle\Core;

class Query
{
	protected $connection;
	protected $className;
	protected $criteria = array();
	protected $limit = null;
	protected $skip = null;

    // Sets Mongo connection
	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	// Adds one or more criteria to the query
	public function where($criteria, $criterion = null)
	{
		if (func_num_args() > 1) {
			$criteria = array((string) $criteria => $criterion);
		}
		// Merge criteria intelligently
		return $this;
	}

	// Runs the query, returning an array of documents up to size $this->limit
	public function all()
	{

	}

	// Runs the query with a limit of 1, returning one document
	public function one()
	{

	}

	// Runs the query as a count query
	public function count()
	{

	}

	// Runs the query as a delete query
	public function delete()
	{

	}

	// Runs the query as an update query, updating the specified fields
	// The many flag indicates whether to update one or many documents
	public function update($fields, $many)
	{

	}

	// Sets a limit
	public function limit($limit)
	{
		$this->limit = $this->boundedInt($limit, 0);
		return $this;
	}

	// Sets a number of results to skip
	public function skip()
	{
		$this->skip = $this->boundedInt($skip, 0);
		return $this;
	}

	// Simple method to get results by page
	public function page($page)
	{
		// Need to do this when the query is run
		// $this->skip = $this->boundedInt($this->limit) * ($page - 1);
		$this->page = $this->boundedInt($page, 1);
	}

	// Clones the query
	public function clone()
	{

	}

	// Converts a value to a non-negative integer
	protected function boundedInt($value, $min)
	{
		return max((int) $value, $min);
	}
}

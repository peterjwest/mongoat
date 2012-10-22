<?php

namespace WhiteOctober\MongoatBundle\Core;

class Mongoat
{
	protected $connection;
	protected $persisting;
    protected $removing;
    protected $modelNamespace;

    // Sets Mongo connection
	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	// Creates a query for a model
	public function find($model)
	{
		return $this->createQuery($this->fullClass($model));
	}

	// Saves one or more documents
	public function save($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);
		foreach ($documents as $document) {
			//...
		}
	}

	// CUpdates one or more documents
	public function update($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);
		foreach ($documents as $document) {
			//...
		}
	}

	// Deletes one or more documents
	public function delete($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);
		foreach ($documents as $document) {
			//...
		}
	}

	// Schedules one or more documents to be saved
	public function persist($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);

        foreach ($documents as $document) {
            $this->persisting[$this->getObjectId($document)] = $document;
        }
	}

	// Schedules one or more models to be deleted
	public function remove($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);

        foreach ($documents as $document) {
            $this->removing[$this->getObjectId($document)] = $document;
        }
	}

	// Saves all persisted models, deletes all removed models
	public function flush()
	{
        //foreach ($this->persisting as $class => $documents) $this->save($documents);
        //foreach ($this->removing as $class => $documents) $this->delete($documents);
        $this->persisting = array();
        $this->removing = array();
	}

	// Gets the full class by prepending the default model namespace
	protected function fullClass($class)
	{
		return strpos($class, $this->modelNamespace) == 0 ? $class : $this->modelNamespace.$class;
	}

	// Groups documents by class
	protected function groupByClass($documents)
	{

	}

	// Gets a unique ID for each object
	protected function getObjectId($object)
	{
		return spl_object_hash($object);
	}
}

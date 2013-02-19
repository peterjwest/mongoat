<?php

namespace WhiteOctober\MongoatBundle\Core;

class Mongoat
{
	protected $currentConnection = null;
	protected $connections = array();
	protected $persisting = array();
    protected $removing = array();
    protected $modelNamespace = '';

    // Model namespace getter/setter
    public function modelNamespace($modelNamespace = null)
    {
    	if (func_num_args() == 0) return $this->modelNamespace;
		$this->modelNamespace = $modelNamespace;
		return $this;
    }

    // Adds a Mongo connection
	public function addConnection($name, $connection)
	{
		// Sets this to be the current connection if none is set already
		if ($this->currentConnection === null) {
			$this->currentConnection = $name;
		}

		$this->connections[$name] = $connection;
	}

	// Getter / setter for the current connection
	public function connection($name = null)
	{
		if (func_num_args() == 0) {
            if (!isset($this->connections[$this->currentConnection])) {
                throw new \Exception("No connections found in ".get_class($this));
            }
            return $this->connections[$this->currentConnection];
        }
        if (!isset($this->connections[$name])) throw new \Exception("Connection '$name' not found in ".get_class($this));

        $this->currentConnection = $name;
	}

    // Gets the specified collection for the current connection
    public function collection($class)
    {
        if (is_object($class)) $class = get_class($class);
        return $this->connection()->collection($this->collectionName($class));
    }

	// Creates an instance of a model, passing it this Mongoat instance
	public function create($model)
	{
		$class = $this->fullClass($model);
		$instance = new $class();
		return $instance->mongoat($this);
	}

	// Creates a find query for a model
	public function find($model)
	{
		$modelClass = $this->fullClass($model);
		$queryClass = $modelClass::$queryClass;
		return new $queryClass($this, $modelClass);
	}

    // Creates an update query for a model
    public function update($model)
    {
        return $this->find($model)->type('update');
    }

    // Creates a delete query for a model
    public function delete($model)
    {
        return $this->find($model)->type('delete');
    }

    public function populate($documents, $name)
    {
        if (count($documents) == 0) return array();
        $document = $documents[0];
        $relationship = $document->schema()->relationship($name);

        $ids = array_map(function($document) { return new \MongoId($document->ownerId()); }, $documents);
        return $this->find($relationship->foreignClass())->where('_id', array('$in' => $ids))->all();
    }

	// Schedules one or more documents to be saved
	public function persist($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);

        foreach ($documents as $document) {
            $this->persisting[spl_object_hash($document)] = $document;
        }
	}

	// Schedules one or more models to be deleted
	public function remove($documents)
	{
		$documents = is_array($documents) ? $documents : array($documents);

        foreach ($documents as $document) {
            $this->removing[spl_object_hash($document)] = $document;
        }
	}

	// Saves all persisted models, deletes all removed models
	public function flush()
	{
        foreach ($this->persisting as $document) $document->save();
        foreach ($this->removing as $document) $document->delete();
        $this->persisting = array();
        $this->removing = array();
	}

    // Gets the full class by prepending the default model namespace
    public function fullClass($class)
    {
        return class_exists($class) ? $class : $this->modelNamespace.'\\'.$class;
    }

    // Getter for the Mongo collection name
    protected function collectionName($class)
    {
        $class = $this->fullClass($class);
        if (!isset($class::$collectionNames[$class])) {
            $class::$collectionNames[$class] = $this->generateCollectionName($class);
        }
        return $class::$collectionNames[$class];
    }

    // Generates a collection name based on the model class
    protected function generateCollectionName($class)
    {
        $namespace = $this->modelNamespace();
        if (strpos($class, $namespace.'\\') == 0) {
            $class = str_replace($namespace.'\\', '', $class);
        }
        return str_replace('\\', '_', $class);
    }
}

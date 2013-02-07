<?php

namespace WhiteOctober\MongoatBundle\Core;
use WhiteOctober\MongoatBundle\Core\Schema;

class Model
{
    static $queryClass = "WhiteOctober\MongoatBundle\Core\Query";
    static $schemaClass = "WhiteOctober\MongoatBundle\Core\Schema";
    static $relationshipClass = "WhiteOctober\MongoatBundle\Core\Relationship";
    static $collectionNames = array();
    static $schemaCaches = array();

    protected $data = array();
	protected $mongoat;
	protected $schema;
    protected $unsaved = true;
    protected $options = array('safe' => true);
    protected $relationships = array();

    // Schema definition
    public function definition($schema)
    {
        return $schema;
    }

    // Getter / setter for Mongoat instance
	public function mongoat($mongoat = null)
	{
        if (func_num_args() == 0) return $this->mongoat;
		$this->mongoat = $mongoat;
        return $this;
	}

    // Getter / setter for Schema instance
    public function schema($schema = null)
    {
        if (func_num_args() == 0) {
            if ($this->schema === null) {
                $class = get_class($this);
                if (!isset(static::$schemaCaches[$class])) {
                    static::$schemaCaches[$class] = $this->definition(new static::$schemaClass($this->mongoat));
                }
                $this->schema = static::$schemaCaches[$class];
            }
            return $this->schema;
        }
        $this->schema = $schema;
        return $this;
    }

    // Gets the ID as a string for the document
    public function id()
    {
        return $this->get('_id');
    }

    // Gets the mongo ID for the document
    public function mongoId()
    {
        return $this->data['_id'];
    }

    // Allows generic getter/setter methods
    public function __call($method, $arguments)
    {
        // Rails style getter/setters using number of arguments
        if ($this->schema()->hasField($method) || $this->schema()->relationship($method)) {
            if (count($arguments) == 0) {
                return $this->get($method);
            }
            if (count($arguments) == 1) {
                return $this->set($method, $arguments[0]);
            }
        }
        // Symfony style getter setters with get/set prefix
        if (substr($method, 0, 3) == "get") {
            return $this->get(lcfirst(substr($method, 3)));
        }
        if (substr($method, 0, 3) == "set") {
            return $this->set(lcfirst(substr($method, 3)), $arguments[0]);
        }

        $class = get_class($this);
        throw new \BadMethodCallException("Undefined method $method() in $class");
    }

    // Get a field by name
    public function get($name)
    {
        if ($this->schema()->hasField($name)) {
            return $this->schema()->filter('get', $name, $this->data[$name]);
        }

        if ($this->schema()->relationship($name)) {
            return $this->relationship($name)->get();
        }

        throw new \Exception("$name field does not exist");
    }

    // Set a field by name
    public function set($name, $value)
    {
        if ($this->schema()->hasField($name)) {
            $this->data[$name] = $this->schema()->filter('set', $name, $value);
            return $this;
        }

        if ($this->schema()->relationship($name)) {
            return $this->relationship($name)->set($value);
        }

        throw new \Exception("$name field does not exist in ".get_class($this));
    }

    // Gets a relationship
    public function relationship($name)
    {
        if ($this->schema()->relationship($name)) {
            // Creates a relationship object if it doesn't exist
            if (!isset($this->relationships[$name])) {
                $schema = $this->schema()->relationship($name);
                $relationship = new static::$relationshipClass($this->mongoat, $this, $schema);
                $this->relationships[$name] = $relationship;
            }
            return $this->relationships[$name];
        }
        throw new \Exception("$name relationship does not exist in ".get_class($this));
    }

    // Getter / setter for whether the model is not saved
    public function unsaved($unsaved = null)
    {
        if (func_num_args() == 0) return $this->unsaved;
        $this->unsaved = $unsaved;
        return $this;
    }

    // Saves a document
    public function save()
    {
        $collection = $this->mongoat()->collection($this);
        $data = $this->schema()->filterCriteria($this->dehydrate());

        // Updates or inserts the model, depending on whether it is unsaved
        if ($this->unsaved()) {
            $response = $collection->insert($data, $this->options);
            $this->unsaved(false);
        }
        else {
            $response = $collection->update(array('_id' => $this->mongoId()), $data, $this->options);
        }

        // Updates relationships
        foreach($this->relationships as $relationship) {
            $relationship->save($data);
        }

        return $response;
    }

    // Deletes a document
    public function delete()
    {
        $collection = $this->mongoat()->collection($this);
        return $collection->remove(array('_id' => $this->mongoId()), $data, $this->options);
    }

    // Sets default fields for the model
    public function defaults()
    {
        foreach ($this->schema()->fields() as $name => $options) {
            $this->set($name, isset($options['default']) ? $options['default'] : null);
        }
        return $this;
    }

    // Dehydrates data to be inserted into the database
    public function dehydrate()
    {
        $data = array();
        foreach($this->schema()->fields() as $name => $options) {
            $value = isset($this->data[$name]) ? $this->data[$name] : (isset($options['default']) ? $options['default'] : null);
            $data[$name] = $this->schema()->filter('dehydrate', $name, $value);
        }
        return $data;
    }

    // Hydrates the data and inserts into the model
    public function hydrate($data = null)
    {
        foreach($this->schema()->fields() as $name => $options) {
            $value = isset($data[$name]) ? $data[$name] : (isset($options['default']) ? $options['default'] : null);
            $this->data[$name] = $this->schema()->filter('hydrate', $name, $value);
        }
        return $this;
    }
}

<?php

namespace WhiteOctober\MongoatBundle\Core;

// This is essentially a wrapper for the Mongo connection.
// Lazily loads Mongo, the database and requested collections
class Connection
{
    protected $server;
    protected $databaseName;
    protected $options = array();
    protected $mongo;
    protected $database;
    protected $collections = array();

    public function __construct($server, $databaseName)
    {
        $this->server = $server;
        $this->databaseName = $databaseName;
    }

    // Instanciates Mongo if it doesn't yet exist then returns it
    public function mongo()
    {
        if (!$this->mongo) $this->mongo = new \Mongo($this->server, $this->options);
        return $this->mongo;
    }

    // Gets the desired Mongo database if it doesn't yet exist then returns it
    public function database()
    {
        if (!$this->database) $this->database = $this->mongo()->selectDB($this->databaseName);
        return $this->database;
    }

    // Gets a Mongo collection by name if it doesn't yet exist then returns it
    public function collection($name)
    {
        if (!isset($this->collections[$name])) {
            $this->collections[$name] = $this->database()->selectCollection($name);
        }
        return $this->collections[$name];
    }
}

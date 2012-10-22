<?php

namespace WhiteOctober\MongoatBundle\Core;

class Document
{
	protected $connection;
	protected $schema;

	public function __construct($connection, $schema)
	{
		$this->connection = $connection;
		$this->schema = $schema;
	}


	public function __call($name, $arguments)
    {
    	$this->getSchema()->hasField($name);

    }

    protected function fromGetterName()
    {

    }

    protected function fromSetterName()
    {

    }
}

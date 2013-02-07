<?php

namespace WhiteOctober\MongoatBundle\Core;
use WhiteOctober\MongoatBundle\Core\Schema;

class Relationship
{
    protected $mongoat;
    protected $model;
    protected $schema;
    protected $value;
    protected $populations = array();
    protected $updates = array();

    public function __construct($mongoat, $model, $schema)
    {
        $this->mongoat = $mongoat;
        $this->model = $model;
        $this->schema = $schema;
    }

    // Creates a query for a relationship
    public function find()
    {
        $query = $this->mongoat->find($this->schema->foreignClass());

        // When this model has the foreign key
        if ($this->schema->foreignKey()) {
            $criteria = $this->model->get($this->schema->fieldName());
            // Wraps multiple foreign keys with $in operator
            if ($this->schema->multiple()) $criteria = array('$in' => $criteria);
            $query->where('_id', $criteria);
        }
        // When the other model has the foreign key
        else {
            $query->where($this->schema->fieldName(), $this->model);
        }

        return $query;
    }

    // Gets a relationship from the database or cache
    // The forceReload flag forces a database request
    public function get()
    {
        if (!isset($this->value)) {
            // Finds the related document(s) from the database
            $query = $this->find();
            $this->value = $this->schema->multiple() ? $query->all() : $query->one();

            // Substitutes existing related objects into the value cache
            foreach ($this->populations as $existing) {
                if ($this->schema->multiple()) {
                    foreach ($this->value as $id => $document) {
                        if ($document->id() === $existing->id()) $this->value[$id] = $existing;
                    }
                }
                else if ($this->value->id() === $existing->id()) $this->value = $existing;
            }

            // Resets populations
            $this->populations = array();

            // If there is an inverse relationship
            if ($this->schema->inverse()) {

                // Tell related objects to populate themselves with this model
                $documents = $this->schema->multiple() ? $this->value : array($this->value);
                foreach ($documents as $document) {
                    $document->relationship($this->schema->inverse())->populate($this->model);
                }
            }
        }

        return $this->value;
    }

    public function set($value)
    {
        // Removes this object from previously related objects, if they have been loaded
        if ($this->schema->inverse() && isset($this->value)) {
            $documents = $this->schema->multiple() ? $this->value : array($this->value);
            foreach ($documents as $document) {
                $document->relationship($this->schema->inverse())->remove($this->model);
            }
        }

        $this->value = $value;
        if ($this->schema->foreignKey()) $this->model->set($this->schema->fieldName(), $this->value);

        // Adds this object to newly related objects
        if ($this->schema->inverse()) {
            $documents = $this->schema->multiple() ? $this->value : array($this->value);
            foreach ($documents as $document) {
                $document->relationship($this->schema->inverse())->add($this->model);
            }
        }

        return $this;
    }

    // Adds a value to the relationship
    public function add($new)
    {
        // Adds the document to the related documents, if it isn't already there
        if ($this->schema->multiple()) {
            $exists = false;
            if (!isset($this->value)) $this->value = array();
            foreach($this->value as $document) {
                if ($document->id() === $new->id()) $exists = true;
            }
            if (!$exists) $this->value[] = $new;
        }
        else $this->value = $new;

        if ($this->schema->foreignKey()) $this->model->set($this->schema->fieldName(), $this->value);
    }

    // Removes a value from the relationship
    public function remove($old)
    {
        if ($this->schema->multiple()) {
            if (!isset($this->value)) $this->value = array();
            $this->value = array_filter($this->value, function($document) use ($old) {
                return $document->id() !== $old->id();
            });
        }
        else $this->value = null;

        if ($this->schema->foreignKey()) $this->model->set($this->schema->fieldName(), $this->value);
    }

    // Saves any changes to the relationship
    public function save()
    {
        return $this;
    }

    // Populates an existing object into a relationship, so related documents use the same instances
    public function populate($value)
    {
        if (!isset($this->value)) {
            $this->populations[] = $value;
        }

        return $this;
    }
}

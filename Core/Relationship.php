<?php

namespace WhiteOctober\MongoatBundle\Core;
use WhiteOctober\MongoatBundle\Core\Schema\Schema;

class Relationship
{
    protected $mongoat;
    protected $model;
    public $schema;
    protected $value;
    protected $instances = array();
    protected $updates = array();

    public function __construct($mongoat, $model, $schema)
    {
        $this->mongoat = $mongoat;
        $this->model = $model;
        $this->schema = $schema;
    }

    // Finds the criteria for a relationship query
    public function criteria()
    {
        $field = $this->schema->foreignKey() ? '_id' : $this->schema->fieldName();
        $criteria = $this->schema->foreignKey() ? $this->model->get($this->schema->fieldName()) : $this->model;

        // Wraps multiple foreign keys with $in operator
        if ($this->schema->multiple()) $criteria = array('$in' => array($criteria));

        return array($field => $criteria);
    }

    // Creates a query for a relationship
    public function find()
    {
        $query = $this->mongoat->find($this->schema->foreignClass());
        return $query->where($this->criteria());
    }

    // Gets a relationship from the database or cache
    // The forceReload flag forces a database request
    public function get()
    {
        if (!isset($this->value)) {
            // Finds the related document(s) from the database
            $query = $this->find();
            $this->value = $this->schema->multiple() ? $query->all() : $query->one();

            // Substitutes existing instances related objects into the value cache
            // TODO: update values here?
            foreach ($this->instances as $existing) {
                if ($this->schema->multiple()) {
                    foreach ($this->value as $id => $document) {
                        if ($document->id() === $existing->id()) $this->value[$id] = $existing;
                    }
                }
                else if ($this->value->id() === $existing->id()) $this->value = $existing;
            }

            $this->instances = array();

            // If there is an inverse relationship
            if ($this->schema->inverse()) {

                // Tell related objects to populate themselves with this model
                $documents = $this->schema->multiple() ? $this->value : array($this->value);
                foreach ($documents as $document) {
                    $document->relationship($this->schema->inverse())->useInstance($this->model);
                }
            }
        }

        return $this->value;
    }

    public function set($value)
    {
        // Clear any updates pending
        $this->updates = array();

        // Removes this object from previously related objects, if they have been loaded
        if ($this->schema->inverse() && isset($this->value)) {
            $documents = $this->schema->multiple() ? $this->value : array($this->value);
            foreach ($documents as $document) {
                $document->relationship($this->schema->inverse())->remove($this->model);
            }

            if ($this->schema->foreignKey()) {
                foreach ($documents as $document) {
                    $document->relationship($this->schema->inverse())->update('remove', $this->model);
                }
            }
            else $this->update('remove', $this->value);
        }

        $this->value = $value;
        if ($this->schema->foreignKey()) $this->model->set($this->schema->fieldName(), $this->value);

        // Adds this object to newly related objects
        if ($this->schema->inverse()) {
            $documents = $this->schema->multiple() ? $this->value : array($this->value);
            foreach ($documents as $document) {
                $document->relationship($this->schema->inverse())->add($this->model);
            }

            if ($this->schema->foreignKey()) {
                foreach ($documents as $document) {
                    $document->relationship($this->schema->inverse())->update('add', $this->model);
                }
            }
            else $this->update('add', $this->value);
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
            $this->value = array_values(array_filter($this->value, function($document) use ($old) {
                return $document->id() !== $old->id();
            }));
        }
        else $this->value = null;

        if ($this->schema->foreignKey()) $this->model->set($this->schema->fieldName(), $this->value);
    }

    // Replaces a queried instance with an existing one, so related documents use the same instances
    public function useInstance($value)
    {
        if (!isset($this->value)) $this->instances[] = $value;
        return $this;
    }

    // Inserts objects into the relationship
    public function populate($objects)
    {
        $values = array();
        if ($this->schema->foreignKey()) {
            $ids = $this->model->get($this->schema->fieldName());
            if (!is_array($ids)) $ids = array($ids);
            foreach($ids as $id) {
                if (isset($objects[$id])) $values[] = $objects[$id];
            }
        }
        else {
            foreach($objects as $object) {
                $ids = $object->get($this->schema->fieldName());
                if (!is_array($ids)) $ids = array($ids);
                foreach($ids as $id) {
                    if ($id == $this->model->id()) {
                        $values[] = $object;
                    }
                }
            }
        }

        if ($this->schema->multiple()) $this->value = $values;
        else $this->value = isset($values[0]) ? $values[0] : null;
    }

    // Schedules an update to be performed on save of the model
    public function update($type, $value)
    {
        $this->updates[] = array('type' => $type, 'value' => $value);
        return $this;
    }

    // Saves any changes to the relationship
    public function save()
    {
        foreach($this->updates as $update) {

            $query = $this->mongoat->update($this->schema->foreignClass());

            $relationshipSchema = $query->schema()->relationship($this->schema->inverse());

            $criteria = $update['value'];
            if ($this->schema->multiple()) $criteria = array('$in' => $criteria);
            $query->where('_id', $criteria);

            if ($relationshipSchema->multiple()) {
                if ($update['type'] == 'add') {
                    $changes = array('$addToSet' => array($this->schema->fieldName() => $this->model));
                }
                if ($update['type'] == 'remove') {
                    $changes = array('$pull' => array($this->schema->fieldName() => $this->model));
                }
            }
            else {
                $value = $update['type'] == 'add' ? $this->model : null;
                $changes = array('$set' => array($this->schema->fieldName() => $value));
            }

            $query->changes($changes);
            $query->all();
        }

        $this->updates = array();
        return $this;
    }
}

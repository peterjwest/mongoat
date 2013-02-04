<?php

namespace WhiteOctober\MongoatBundle\Core\Relationships;

class HasMany extends Relationship
{
    protected $foreignKey = false;
    protected $multiple = true;

    function get($document) {
        return $this->mongoat
            ->find($this->options['class'])
            ->where($this->options['fieldName'], $document)
            ->all();
    }

    function set($document, $relations) {
        // Updates related models if there is an inverse relationship
        if (isset($this->options['inverse'])) {

            // Removes old relations
            foreach ($document->get($this->name) as $relation) {
                $relation->remove($this->options['inverse'], $document);
            }

            // Adds new relations
            foreach ($relations as $relation) {
                $relation->add($this->options['inverse'], $document);
            }
        }

        // Schedules the relationship to be updated in the databae on save
        $document->scheduleUpdate($this->name, $relations);

        return $document;
    }

    function update($document, $relations) {
        // Removes relationship from old relations
        $this->mongoat->update($this->options['class'])
            ->where($this->options['fieldName'], $document)
            ->changes(array('$set' => array($this->options['fieldName'] => null)))
            ->all();

        // Adds relationship to new relations
        $this->mongoat->update($this->options['class'])
            ->where('_id', array('$in' => $relations))
            ->changes(array('$set' => array($this->options['fieldName'] => $document)))
            ->all();
    }

    function add($document, $relation) {
        return $document->set($this->name, $relation);
    }

    function remove($document) {
        return $document->set($this->name, null);
    }
}

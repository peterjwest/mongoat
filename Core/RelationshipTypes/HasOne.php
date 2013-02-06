<?php

namespace WhiteOctober\MongoatBundle\Core\RelationshipTypes;

class HasOne extends RelationshipType
{
    protected $foreignKey = false;
    protected $multiple = false;

    function get($document) {
        return $this->mongoat
            ->find($this->options['class'])
            ->where($this->options['fieldName'], $document)
            ->one();
    }

    function set($document, $relation) {
        // Updates related models if there is an inverse relationship
        if (isset($this->options['inverse'])) {
            $oldRelation = $document->get($this->name);
            $oldRelation->remove($this->options['inverse'], $document);
            $relation->add($this->options['inverse'], $document);
        }

        // Schedules the relationship to be updated in the databae on save
        $document->scheduleUpdate($this->name, $relation);

        return $document;
    }

    function update($document, $relation) {
        // Removes relationship from old relation
        $this->mongoat->update($this->options['class'])
            ->where($this->options['fieldName'], $document)
            ->changes(array('$set' => array($this->options['fieldName'] => null)))
            ->all();

        // Adds relationship to new relation
        $this->mongoat->update($this->options['class'])
            ->where('_id', $relation)
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

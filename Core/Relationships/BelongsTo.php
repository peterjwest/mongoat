<?php

namespace WhiteOctober\MongoatBundle\Core\Relationships;

class BelongsTo extends Relationship
{
    protected $foreignKey = true;
    protected $multiple = false;

    function get($document) {
        return $this->mongoat
            ->find($this->options['class'])
            ->where('_id', $document->get($this->options['fieldName']))
            ->one();
    }

    function set($document, $relation) {
        if ($this->options['inverse']) {
            $relation->add($this->options['inverse'], $document);
        }
        return $document->set($this->options['fieldName'], $relation);
    }

    function add($document, $relation) {
        // add update to cache
        //return $document->set($this->options['fieldName'], $relation);
    }

    function remove($document) {
        // add update to cache
        //return $document->set($this->options['fieldName'], null);
    }
}

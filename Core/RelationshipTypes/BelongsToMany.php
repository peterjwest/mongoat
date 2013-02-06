<?php

namespace WhiteOctober\MongoatBundle\Core\RelationshipTypes;

class BelongsToMany extends RelationshipType
{
    protected $foreignKey = true;
    protected $multiple = true;

    function get($document) {
        return $this->mongoat
            ->find($this->options['class'])
            ->where('_id', array('$in' => $document->get($this->options['fieldName'])))
            ->all();
    }

    function set($document, $relations) {
        if ($this->options['inverse']) {
            foreach($relations as $relation) {
                $relation->add($this->options['inverse'], $document);
            }
        }
        return $document->set($this->options['fieldName'], $relations);
    }

    function add($document, $relation) {
        // $relations = $document->get($options['name']);
        // $testRelations = array_filter($relations, function($existing) use ($relation) {
        //     return $existing->id() !== $relation->id();
        // });
        // if (count($relations) == count($testRelations)) $relations[] = $relation;
        // return $document->set($options['name'], $relations);
    }

    function remove($document) {
        // $relations = $document->get($options['name']);
        // $testRelations = array_filter($relations, function($existing) use ($relation) {
        //     return $existing->id() !== $relation->id();
        // });
        // if (count($relations) == count($testRelations)) $relations[] = $relation;
        // return $document->set($options['name'], $relations);
    }
}

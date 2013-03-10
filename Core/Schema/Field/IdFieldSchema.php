<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Field;

use WhiteOctober\MongoatBundle\Core\Model;

class IdFieldSchema extends FieldSchema
{
    // This converts a passed string to a MongoId object
    public function set($id)
    {
        if ($id instanceof \MongoId) return $id;
        if ($id instanceof Model) return $id->mongoId();
        if ($id === true) return new \MongoId();
        if ($id === null) return null;
        return new \MongoId($id);
    }

    // This returns the Id value of a MongoId
    public function get($id)
    {
        return (string) $id;
    }
}

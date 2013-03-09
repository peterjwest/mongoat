<?php

namespace WhiteOctober\MongoatBundle\Core\Schema\Field;

use WhiteOctober\MongoatBundle\Core\Model;

class DateFieldSchema extends FieldSchema
{
    // Converts a timestamp, date string, or MongoDate to a DateTime object
    public function set($value)
    {
        if (is_string($value)) return new \DateTime($value);
        if (is_int($value)) {
            $date = new \DateTime();
            $date->setTimestamp($value);
            return $date;
        }
        if ($value instanceof \MongoDate) {
            return $this->hydrate($value);
        }
        return $value;
    }

    // Converts a DateTime object to a MongoDate
    public function dehydrate($value)
    {
        return $value instanceof \DateTime ? new \MongoDate($value->getTimestamp()) : null;
    }

    // Converts a MongoDate object to a DateTime
    public function hydrate($value)
    {
        if ($value instanceof \MongoDate) {
            $date = new \DateTime();
            $date->setTimestamp($value->sec);
            return $date;
        }
        return null;
    }
}

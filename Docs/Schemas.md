# Schemas

Schemas allow you to define the fields and relationships on a model.
Schemas in Mongoat are normally defined in the model.

    class User extends Model
    {
        public function definition($schema)
        {
            $schema->fields(array(
                'name' => array('type' => 'string'),
                'score' => array('type' => 'integer')
            ));

            $schema->relationships(
                'cat' => array('type' => 'hasOne', 'class' => 'Cat'),
                'team' => array('type' => 'belongsTo', 'class' => 'Team'),
            ));

            return $schema;
        }
    }

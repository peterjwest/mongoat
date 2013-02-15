# Models

## Sample model class

    // Classes extend a Model class in the bundle, which extends a base Model class in Mongoat
    class User extends Model
    {
        // The class is linked to a query class, defaults to the default Mongoat query class
        static $queryClass = 'WhiteOctober\MongoatBundle\Core\Query';

        // Override the definition method to define the schema, can be used to add to an inherited schema
        public function definition($schema)
        {
            // Each model has a schema object, which tells the model what fields it has
            // This method defines the schema on instantiation, however we could define or update it anywhere
            $schema = self::parent($schema);

            // Normal fields declared as below, with support for Symfony validation
            $schema->fields(array(
                'foo' => array('type' => 'string'),
                'bar' => array('type' => 'number', 'validation' => array())
            ));

            // All kinds of relationships defined as below
            $schema->relationships(
                'cat' => array('type' => 'hasOne', 'class' => "Cat"),
                'team' => array('type' => 'belongsTo', 'class' => "Team", 'fieldName' => 'petId'),
                'phone_number' => array('type' => 'containsMany', 'class' => "PhoneNumber"),
                'group' => array('type' => 'embeddedIn', 'class' => "UserGroup")
            ));

            return $schema;
        }
    }


## Model methods

    // Models have Symfony2 and Rails style getter/setters fields:

    $user->setAge(21);
    $age = $user->getAge();

    $user->age(21);
    $age = $user->age();

    // Models also have string and array based getters/setters:

    $user->set('age', 21);
    $age = $user->get('age');

    $user->set(array('age' => 21, 'registered' => new \DateTime));
    $details = $user->get(array('age', 'registered'));

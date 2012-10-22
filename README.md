# Mongoat

A straight forward Mongo ODM (ORM) for Symfony2


## Planned features

- Define your schema in code, in the model
- Consequently, no code generation
- Populate relationships to optimise queries, even nested relationships
- Generate migration files automatically to migrate code easily
- Migrate your database forwards or backwards

## TODO

- Build field update/insert generator
- Finish model getters/setters
- Hook into Mongo connection
- Get prototype working
- Switch to TDD, test existing code
- Add relationships
- Add population feature
- Build array walker to filter query criteria
- Add support for symfony validation
- Add logging
- Add query analyser

## Planned interface:

### Sample class:

	// Classes extend a Model class in the bundle, which extends a base Model class in Mongoat
	class User extends Model
	{
		// Override the constructor to define the schema, can be used to add to an inherited schema
		public function __construct($connection)
		{
			self::parent($connection);

			// The class is linked to a query class, defaults to the default Mongoat query class
			$this->setQueryClass('UserQuery');

			// Each model has a schema object, which tells the model what fields it has
			// Although we change this in the constructor, we could techincally change it anywhere
			$schema = $this->getSchema();

			// Normal fields declared here, with Symfony validation
			$schema->fields(array(
				'foo' => array('type' => 'string', 'validation' => array()),
				'bar' => array('type' => 'number', 'validation' => array())
			));

			// Any kinds of relationships defined here
			$schema->relationships(
				'cat' => array('type' => 'hasOne', 'class' => "Cat"),
				'team' => array('type' => 'belongsTo', 'class' => "Team"),
				'phone_number' => array('type' => 'containsMany', 'class' => "PhoneNumber"),
				'group' => array('type' => 'embeddedIn', 'class' => "UserGroup")
			));
		}
	}

### Sample query class:

	// User query class defines query methods, inherits in a similar way to the models
	class UserQuery extends Query
	{
		public function inPast()
		{
			return $this->where(array('date' => array('$lt' => new \DateTime)));
		}
	}

### Sample query:

	// From the service we can get a new query object
	$query = $container->get("mongoat")->find("User");

	// The query object lets you chain standard and custom methods
	$users = $query
		// All criteria are filtered by existing fields and types,
		// models are converted to ID and dates are converted to mongo dates
		->where(array('age' => 21, 'registered' => array('$lt' => new DateTime)))
		->inPast()
		// Relationships can be pre-populated to optimise queries, even nested relationships work
		// embedded relationships are included by default, but can be named to make nested relationships populate
		->populate(array('cat' => array('breed', 'vaccinations'), 'group'))
		->all();

### Model methods

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


### Standalone population

You can get the relationships for an array of models after they've been loaded:

	// This loads the users
	$users = $container->get("mongoat")->find("User")->all();

	// This loads the relationships into each user and also returns the queried objects
	$cats = $container->get("mongoat")->populate($users, 'cat');

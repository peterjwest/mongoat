# Mongoat

A straight forward Mongo ODM (ORM) for Symfony2


## Features

- Simple, concise, well tested code
- Complex relationships with simple caching and no hassle
- Populate relationships with join-like queries
- Inherit any part of Mongoat in your project
- Define your schema in code, in the model
- No code generation

### Planned:

- Generate migration files automatically to migrate data easily
- Migrate your database forwards or backwards
- More Symfony integration


## Getting started

Once you have Mongoat loaded as a dependency, you need to define its configuration:

### Symfony config

	mongoat:
	    model_namepsace: Acme\YourBundle\Model
	    connections:
	        local:
	            server:   mongodb://localhost:27017
	            database: your_database
	        external:
	        	class:    Acme\YourBundle\Connection
	        	server:   mongodb://somewhere_else:27017
	            database: your_other_database

### Standalone setup

	$mongoat = new Mongoat();
	$mongoat->modelNamespace('Acme\YourBundle\Model');

	$connection = new Connection('mongodb://localhost:27017', 'your_database');
	$mongoat->addConnection('local', $connection);


The model namespace setting allows you to use relative paths to access models through Mongoat e.g. `$mongoat->find('User');` instead of `$mongoat->find('Acme\YourBundle\Model\User');`.
You can still use the full path to access models in other namespaces.

You can define one or more connections, in symfony you can define a custom class to be used for the connection.
The first connection defined is the default, switch connections using `$mongoat->connection('connection_name');`.


## Simple example

Define a model by extending Mongoat's Model class.

	namespace Acme\YourBundle\Model;
	use WhiteOctober\MongoatBundle\Core\Model;

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

Create and save a new user:

	$user = $mongoat->create('User');
	$user->name('John');
	$user->score(33);
	$user->save();

Find that user again:

	$query = $mongoat->find('User');
	$user = $query->where('name', 'John')->one();


## Interface

### Sample model class

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

	// Mongoat is registered on the container
	$mongoat = $container->get("mongoat");

	// From the service we can get a new query object, the default query action is find
	$query = $mongoat->get("User");

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

### Other query methods:

	// Update and delete queries are run with one() or all() the same as find()
	$query = $mongoat->get("User")->where(array('lame' => true))->update(array('epic' => false))->one();
	$query = $mongoat->get("User")->where(array('epic' => false))->delete()->all();

	// Count queries are run immediately
	$query = $mongoat->get("User")->where(array('epic' => false))->count();

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
	$users = $mongoat->get("User")->all();

	// This loads the relationships into each user and also returns the queried objects
	$cats = $mongoat->populate($users, 'cat');


## TODO

- Build field update/insert generator
- Complete population feature
- Add support for symfony validation
- Add logging
- Add query analyser
- Support additional operators: $slice, $regex, $where, $elemMatch, $bit, $mod
- Tests:
	- Schema
		- Relationships
	- Model
		- Relationships
		- Creating/saving, unsaved() method
		- Deleting
		- Hydrate/dehydrate
	- Mongoat
		- Test model namespaces / full class method
		- Test connections
		- Making find query
		- Making update query
		- Making delete query
		- Unit of work
		- Populating
	- Query
		- where() method
		- fields() method
		- limit(), skip(), page()
		- Populating
		- Find: one, all
		- Update: one, all
		- Delete: one, all

# Mongoat

A straight forward Mongo ODM (ORM) for Symfony2


## Planned features

- Define your schema in code, in the model
- Consequently, no code generation
- Include references to optimise queries, even nest references
- Generate migration files automatically to migrate code easily
- Migrate your database forwards or backwards


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
			$schema->addFields(array(
				'foo' => array('type' => 'string', 'validation' => array()),
				'bar' => array('type' => 'number', 'validation' => array())
			));

			// Any kinds of references defined here
			$schema->addReferences(
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
	$query = $container->get("model")->find("User");

	// The query object lets you chain standard and custom methods
	$users = $query
		// All criteria are filtered by existing fields and types,
		// models are converted to ID and dates are converted to mongo dates
		->where(array('age' => 21, 'registered' => array('$lt' => new DateTime)))
		->inPast()
		// References can be included to optimise queries, even nested references work
		// embedded references are included by default
		->references(array('cat' => array('breed', 'vaccinations'), 'group'))
		->all();

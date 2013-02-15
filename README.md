# Mongoat

A straight forward Mongo ODM (ORM) for Symfony2


## Menu

- [Configuration - configuring Mongoat with Symfony2 or standalone](Docs/Config.md)
- [Schemas - defining fields and relationships](Docs/Schemas.md)
- [Queries - finding and updating documents](Docs/Queries.md)
- [Models - getting and setting, saving and loading data](Docs/Models.md)
- [Relationships - getting and setting related documents](Docs/Relationships.md)
- [Populating - getting related documents efficiently](Docs/Populating.md)

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

Find that user again and delete them:

	$query = $mongoat->find('User');
	$user = $query->where('name', 'John')->one();
	$user->delete();


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

# Queries

## Using queries:

To query a model, new query objects are created with Mongoat. They can be find, update or delete queries.

    $query1 = $mongoat->find("User");
    $query2 = $mongoat->update("User");
    $query3 = $mongoat->delete("User");

Queries have a number of chainable methods you can use to configure them:

    $query
        ->where(array('age' => 21, 'registered' => array('$lt' => new DateTime)))
        ->fields(array('name'))
        ->limit(5);

You can run the query on the first matched document:

    $user = $query->one();

You can run the query on all documents:

    $users = $query->all();

You can count the documents that will match the query:

    $userCount = $query->count();


## Custom query classes:

By defining a custom query class, you can add custom methods to use with your queries.
These can be chainable, modular methods such as inPast(), or methods which run the query and return data values.

    class UserQuery extends Query
    {
        public function inPast()
        {
            return $this->where(array('date' => array('$lt' => new \DateTime)));
        }

        public function findSomeNamed($count, $name)
        {
            return $this->where('name', $name)->limit($count)->all();
        }
    }

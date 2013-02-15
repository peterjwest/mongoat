# Getting started

Once you have Mongoat loaded as a dependency, you need to define its configuration:

## Symfony config

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

## Standalone setup

    $mongoat = new Mongoat();
    $mongoat->modelNamespace('Acme\YourBundle\Model');

    $connection = new Connection('mongodb://localhost:27017', 'your_database');
    $mongoat->addConnection('local', $connection);


The model namespace setting allows you to use relative paths to access models through Mongoat e.g. `$mongoat->find('User');` instead of `$mongoat->find('Acme\YourBundle\Model\User');`.
You can still use the full path to access models in other namespaces.

You can define one or more connections, in symfony you can define a custom class to be used for the connection.
The first connection defined is the default, switch connections using `$mongoat->connection('connection_name');`.

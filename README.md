# Database Replicator for Nette

[![Build Status](https://travis-ci.org/pmgdev/database-replicator.svg?branch=master)](https://travis-ci.org/pmgdev/database-replicator-nette)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pmgdev/database-replicator-nette/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pmgdev/database-replicator-nette/?branch=master)
[![Downloads this Month](https://img.shields.io/packagist/dm/pmgdev/database-replicator-nette.svg)](https://packagist.org/packages/pmgdev/database-replicator-nette)
[![Latest stable](https://img.shields.io/packagist/v/pmgdev/database-replicator-nette.svg)](https://packagist.org/packages/pmgdev/database-replicator-nette)
[![Coverage Status](https://coveralls.io/repos/github/pmgdev/database-replicator-nette/badge.svg?branch=master)](https://coveralls.io/github/pmgdev/database-replicator-nette?branch=master)

This repository provide [DatabaseReplicator](https://github.com/pmgdev/database-replicator) and better integration to nette by extension. Where is written what needs to be implemented for smooth running.

### Install by composer

```bash
composer require --dev pmgdev/database-replicator-nette
```

### Before start
When everybody can have different database layout, then this extension is abstract class [DatabaseReplicatorExtension24](src/DatabaseReplicatorExtension24.php).

In first step you must extend this class and implement method **buildDatabaseFactory**. It is easy:

```php
use PmgDev\DatabaseReplicator\DatabaseReplicatorExtension24

class MyDatabaseReplicatorExtension extends DatabaseReplicatorExtension24
{

	protected function buildDatabaseFactory(
		DI\ServiceDefinition $connectionFactory,
		string $replicatorService,
		string $name
	): DI\ServiceDefinition
	{
		return $connectionFactory
			->setFactory(MyConnectionFactory::class) // MyConnectionFactory is in DatabaseReplicator README above
			->setArguments([$replicatorService]);
	}

}
```

That's all and register new extension.

```neon
extensions:
	databaseReplicator: MyDatabaseReplicatorExtension # use our new extension class

databaseReplicator:
	sourceFile: %appDir%/../db/structure.sql
	admin:
		database: postgres
		username: postgres
		host: localhost
		password: dummy
		port: 5432
	connections:
		test_db: # source database name
			# all properties copy from admin
			username: user
			password: dummy
			# database: you can set other database name 
		test_data:
			# next test database with dummy data
	tempDir: %tempDir%
	# psql: # default is /usr/bin/psql
```

Now we have available service **databaseReplicator.test_db.database** and **databaseReplicator.test_data.database** instance of `PmgDev\DatabaseReplicator\Database`

```php
$database = $container->getService('databaseReplicator.test_db.database');
$connection = $database->create(); // create database
// make tests
$database->drop();
```

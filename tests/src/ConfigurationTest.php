<?php declare(strict_types=1);

use Nette\DI\Container;
use PmgDev\DatabaseReplicator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
test(function (Container $container) {
	$adminConfig = $container->getService('databaseReplicator.admin');
	Assert::same([
		'database' => 'postgres',
		'username' => 'postgres',
		'password' => 'dummy',
		'host' => 'example.com',
		'port' => 5433,
	], get_object_vars($adminConfig));
	Assert::type(DatabaseReplicator\Config::class, $adminConfig);

	$command = $container->getService('databaseReplicator.command');
	Assert::type(DatabaseReplicator\Database\Postgres\Command::class, $command);

	$commandFactory = $container->getService('databaseReplicator.command.factory');
	Assert::type(DatabaseReplicator\Database\Postgres\CommandFactory::class, $commandFactory);

	$config = $container->getService('databaseReplicator.test_db.config');
	Assert::same([
		'database' => 'test_db',
		'username' => 'user',
		'password' => 'passwd',
		'host' => 'example.com',
		'port' => 5433,
	], get_object_vars($config));
	Assert::type(DatabaseReplicator\Config::class, $config);

	$database = $container->getService('databaseReplicator.test_db.database');
	Assert::type(DatabaseReplicator\Database::class, $database);

	$prefix = $container->getService('databaseReplicator.test_db.database.prefix');
	Assert::type(DatabaseReplicator\Database\Prefix::class, $prefix);

	$replicator = $container->getService('databaseReplicator.test_db.database.replicator');
	Assert::type(DatabaseReplicator\Database\Replicator::class, $replicator);

	$replicatorFiles = $container->getService('databaseReplicator.test_db.database.replicator.files');
	Assert::type(DatabaseReplicator\Source\Files::class, $replicatorFiles);

	$sourceDatabase = $container->getService('databaseReplicator.test_db.source.database');
	Assert::type(DatabaseReplicator\Source\Database::class, $sourceDatabase);

	$sourceHash = $container->getService('databaseReplicator.test_db.source.hash');
	Assert::type(DatabaseReplicator\Source\Hash::class, $sourceHash);

	$sourceFiles = $container->getService('databaseReplicator.test_db.source.hash.files');
	Assert::type(DatabaseReplicator\Source\Files::class, $sourceFiles);
});

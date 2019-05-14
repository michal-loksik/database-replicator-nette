<?php declare(strict_types=1);

use Nette\DI\Container;
use PmgDev\DatabaseReplicator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
test(function (Container $container) {
	/** @var DatabaseReplicator\DatabaseOperation $operation */
	$operation = $container->getService('databaseReplicator.database.operation');
	Assert::type(DatabaseReplicator\DatabaseOperation::class, $operation);
	$operation->drop('foo');
	$count = 0;
	$operation->build(TRUE, function () use (&$count) {
		++$count;
	}, function () use (&$count) {
		++$count;
	});

	Assert::same(2, $count);
	$operation->dropAll(FALSE, function () use (&$count) {
		++$count;
	});
	Assert::same(3, $count);
}, [
	'databaseReplicator.command' => DatabaseReplicator\DummyCommand::class,
]);
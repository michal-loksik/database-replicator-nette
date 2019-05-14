<?php

namespace PmgDev\DatabaseReplicator;

use Nette\DI;

class DummyExtension24 extends DatabaseReplicatorExtension24
{

	protected function buildDatabaseFactory(
		DI\ServiceDefinition $connectionFactory,
		string $replicatorService,
		string $name
	): DI\ServiceDefinition
	{
		return $connectionFactory
			->setFactory(DummyConnectionFactory::class)
			->setArguments([$replicatorService]);
	}

}

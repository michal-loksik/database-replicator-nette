<?php

namespace PmgDev\DatabaseReplicator;

use Nette\DI;

class DummyExtension24 extends DatabaseReplicatorExtension24
{

	protected function buildDatabaseConnection(
		DI\ServiceDefinition $database,
		string $name,
		string $replicatorService
	): void
	{
		$database
			->setFactory(DummyDatabase::class)
			->setArguments([$replicatorService]);
	}

}

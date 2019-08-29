<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Source\Files;

class DummyCommand implements Command
{

	public function drop(string $database): void
	{
	}


	public function copy(Config $config, string $cloneDb): void
	{
	}


	public function existsDatabase(string $database): bool
	{
		return FALSE;
	}


	public function listDatabases(): iterable
	{
		return [];
	}


	public function create(Config $config): void
	{
	}


	public function importFiles(Files $filenames, Config $config): void
	{
	}

}

<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

class DummyDatabase extends Database
{

	protected function createConnection(Config $config)
	{
		return new \stdClass();
	}


	protected function disconnectConnection($connection): void
	{
	}

}

<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Database\Replicator;

class DummyConnectionFactory implements ConnectionFactory
{

	/** @var Replicator */
	private $replicator;


	public function __construct(Replicator $replicator)
	{
		$this->replicator = $replicator;
	}


	public function create()
	{
		return new \stdClass();
	}


	public function drop($connection): void
	{
	}

}

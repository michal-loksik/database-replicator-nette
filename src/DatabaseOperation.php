<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\DI\Container;
use PmgDev\DatabaseReplicator\Source;

class DatabaseOperation
{
	/** @var Container */
	private $container;

	/** @var string */
	private $prefix = '';

	/** @var string[] */
	private $connectionsName = [];


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	final public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
	}


	/**
	 * @param string[] $connectionsName
	 */
	final public function setConnectionsName(array $connectionsName): void
	{
		$this->connectionsName = $connectionsName;
	}


	public function drop(string $database): void
	{
		$this->command()->drop($database);
	}


	/**
	 * @param bool|string[] $keepSourceDatabases
	 * @param callable|NULL $onAfterDrop
	 */
	public function dropAll($keepSourceDatabases = TRUE, ?callable $onAfterDrop = NULL): void
	{
		$this->run(function (string $name) use ($keepSourceDatabases, $onAfterDrop): void {
			$databases = $this->databaseReplicator($name)->clearDatabases($this->checkKeepDatabases($name, $keepSourceDatabases));
			if ($onAfterDrop !== NULL) {
				$onAfterDrop($name, $databases);
			}
			$this->sourceFile($name)->removeActiveFile();
		});
	}


	public function build(bool $isForce, ?callable $onStart = NULL, ?callable $onEnd = NULL): void
	{
		$this->run(function (string $name) use ($isForce, $onStart, $onEnd): void {
			if ($onStart !== NULL) {
				$onStart();
			}
			$sourceDatabase = $this->sourceDatabase($name);
			if ($isForce) {
				$this->databaseReplicator($name)->clearDatabases(FALSE);
			}
			$exists = $sourceDatabase->build();
			if ($onEnd !== NULL) {
				$prefix = $this->prefix($name);
				$onEnd($name, $exists, $prefix->config());
			}
		});
	}


	final protected function run(callable $function): void
	{
		foreach ($this->connectionsName as $name) {
			$function($name);
		}
	}


	final protected function command(): Command
	{
		return $this->getService('command');
	}


	final protected function databaseReplicator(string $name): Database\Replicator
	{
		return $this->getService("$name.database.replicator");
	}


	final protected function sourceDatabase(string $name): Source\Database
	{
		return $this->getService("$name.source.database");
	}


	final protected function sourceFile(string $name): Source\Hash
	{
		return $this->getService("$name.source.hash");
	}


	final protected function prefix(string $name): Database\Prefix
	{
		return $this->getService("$name.database.prefix");
	}


	final protected function getService(string $name)/*: object*/
	{
		return $this->container->getService($this->prefix . $name);
	}


	/**
	 * @param string $name
	 * @param string[]|bool $keepSourceDatabases
	 * @return string[]|bool
	 */
	protected function checkKeepDatabases(string $name, $keepSourceDatabases)
	{
		return $keepSourceDatabases;
	}

}

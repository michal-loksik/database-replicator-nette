<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\DI;
use Nette\DI\Definitions\ServiceDefinition;
use PmgDev\DatabaseReplicator\Source;

abstract class DatabaseReplicatorExtension24 extends DI\CompilerExtension
{
	private const DB_SETTINGS = [
		'database' => '',
		'username' => '',
		'host' => '',
		'password' => '',
		'port' => 5432,
	];

	private const DEFAULTS = [
		'admin' => self::DB_SETTINGS,
		'connections' => [],
		'sourceFile' => [],
		// optional
		'psql' => '/usr/bin/psql',
		'tempDir' => '/tmp/database.replicator',
	];


	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->getDefaults());

		$adminConfig = $this->buildConfig('admin', $config['admin']);
		$command = $this->buildCommand($adminConfig, $config['psql']);
		$this->buildDatabaseOperation(array_keys($config['connections']));

		$autowire = count($config['connections']) < 2;
		foreach ($config['connections'] as $name => $connection) {
			if (!isset($connection['database']) || $connection['database'] === '') {
				$connection['database'] = $name;
			}

			$dbconfig = $this->buildConfig($name . '.config', $connection + $config['admin']);

			$sourceFile = $this->buildSourceFile($name, $config['sourceFile'], $config['tempDir']);

			$prefix = $this->buildDatabasePrefix($name, $dbconfig, $sourceFile);

			$sourceDatabase = $this->buildSourceDatabase($name, $prefix, $sourceFile, $command);

			$this->buildDatabaseReplicator($name, $command, $prefix, $sourceDatabase);

			$this->buildDatabase($name, $autowire);
		}
	}


	protected function getDefaults(): array
	{
		return self::DEFAULTS;
	}


	private function replicatorName(string $name, $service = FALSE): string
	{
		return $this->prefix(($service ? ('@' . $name) : $name) . '.database.replicator');
	}


	/**
	 * @param string $name
	 * @param string[] $config
	 */
	private function buildConfig(string $name, array $config): DI\ServiceDefinition
	{
		return $this->getContainerBuilder()->addDefinition($this->prefix($name))
			->setFactory(Config::class)
			->setArguments([
				$config['database'],
				$config['username'],
				$config['password'],
				$config['host'],
				$config['port'],
			])
			->setAutowired(FALSE);
	}


	private function buildDatabase(string $name, bool $autowire): void
	{
		$database = $this->getContainerBuilder()
			->addDefinition($this->prefix($name . '.database'))
			->setAutowired($autowire);
		$this->buildDatabaseConnection($database, $name, $this->replicatorName($name, TRUE));
	}


	abstract protected function buildDatabaseConnection(ServiceDefinition $service, string $name, string $replicatorService): void;


	private function buildDatabaseReplicator(
		string $name,
		DI\ServiceDefinition $command,
		DI\ServiceDefinition $prefix,
		DI\ServiceDefinition $sourceDatabase
	): void
	{
		$builder = $this->getContainerBuilder();
		$files = $builder->addDefinition($this->prefix($name . '.database.replicator.files'))
			->setFactory(Source\Files::class)
			->setAutowired(FALSE);

		$builder->addDefinition($this->replicatorName($name))
			->setFactory(Database\Replicator::class)
			->setArguments([$command, $prefix, $sourceDatabase, $files])
			->setAutowired(FALSE);
	}


	private function buildSourceFile(
		string $name,
		string $sourceFile,
		string $tempDir
	): DI\ServiceDefinition
	{
		$builder = $this->getContainerBuilder();
		$files = $builder->addDefinition($this->prefix($name . '.source.hash.files'))
			->setFactory(Source\Files::class, [[$sourceFile]])
			->setAutowired(FALSE);

		return $builder->addDefinition($this->prefix($name . '.source.hash'))
			->setFactory(Source\Hash::class)
			->setArguments([$name, $tempDir, $files])
			->setAutowired(FALSE);
	}


	private function buildSourceDatabase(
		string $name,
		DI\ServiceDefinition $prefix,
		DI\ServiceDefinition $sourceFile,
		DI\ServiceDefinition $command
	): DI\ServiceDefinition
	{
		return $this->getContainerBuilder()->addDefinition($this->prefix($name . '.source.database'))
			->setFactory(Source\Database::class)
			->setArguments([$prefix, $sourceFile, $command])
			->setAutowired(FALSE);
	}


	private function buildDatabasePrefix(
		string $name,
		DI\ServiceDefinition $dbconfig,
		DI\ServiceDefinition $sourceFile
	): DI\ServiceDefinition
	{
		return $this->getContainerBuilder()->addDefinition($this->prefix($name . '.database.prefix'))
			->setFactory(Database\Prefix::class)
			->setArguments([$dbconfig, $sourceFile])
			->setAutowired(FALSE);
	}


	private function buildCommand(
		DI\ServiceDefinition $adminConfig,
		string $psql
	): DI\ServiceDefinition
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('command.factory'))
			->setFactory(Database\Postgres\CommandFactory::class, [$psql])
			->setAutowired(FALSE);

		return $builder->addDefinition($this->prefix('command'))
			->setFactory(new DI\Statement([$this->prefix('@command.factory'), 'create'], [$adminConfig]))
			->setAutowired(FALSE);
	}


	private function buildDatabaseOperation(array $connectionsName): DI\ServiceDefinition
	{
		return $this->getContainerBuilder()->addDefinition($this->prefix('database.operation'))
			->setFactory(DatabaseOperation::class)
			->addSetup('setPrefix', [$this->prefix('')])
			->addSetup('setConnectionsName', [$connectionsName])
			->setAutowired(FALSE);
	}

}

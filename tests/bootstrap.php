<?php

declare(strict_types=1);

use Nette\DI;
use PmgDev\DatabaseReplicator;

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

define('TEMP_DIR', __DIR__ . '/temp');
@mkdir(TEMP_DIR);

function test(\Closure $function, array $services = []): void
{
	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	databaseReplicator:
		sourceFile: %appDir%/data/structure.sql
		admin:
			database: postgres
			username: postgres
			host: example.com
			password: dummy
			port: 5433
		connections:
			test_db:
				username: user
				password: passwd
		tempDir: %tempDir%
	', 'neon'));
	$compiler = new DI\Compiler;
	$compiler->addExtension('databaseReplicator', new DatabaseReplicator\DummyExtension24());
	$compiler->addConfig([
		'parameters' => [
			'appDir' => TEMP_DIR . '/..',
			'tempDir' => TEMP_DIR,
		],
		'services' => $services,
	]);

	$class = $compiler->addConfig($config)->setClassName('Container1')->compile();
//	$container = TEMP_DIR . DIRECTORY_SEPARATOR . 'container.php';
//	file_put_contents($container, '<?php ' . $class);
//	require $container;
	eval($class);
	$container = new Container1;
	$container->initialize();
	$function($container);
}

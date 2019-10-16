<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:23
 */
define('CURRENT_DATE', '2019-11-01');
$dirName = dirname(__DIR__);
chdir($dirName);
require 'vendor/autoload.php';

define('ROOT_PATH', $dirName.'/');

use Symfony\Component\Console\Application;

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';
$application = new Application('Application console');

$commands = $container->get('config')['console']['commands'];
foreach ($commands as $command) {
    $application->add($container->get($command));
}

$application->run();
<?php
declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Extension;

class DatabaseMigrationExtension extends Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
//        Events::TEST_FAIL    => 'testFail',
//        Events::SUITE_AFTER  => 'afterSuite',
    ];

    public function beforeSuite(): void
    {
        try {
            /** @var \Codeception\Module\Cli $cli */
            $cli = $this->getModule('Cli');

            $this->writeln('Recreating the DB...');
            $cli->runShellCommand('cd '.codecept_root_dir().' && ./vendor/bin/doctrine orm:schema-tool:drop --force');
            $cli->seeResultCodeIs(0);
            $cli->runShellCommand('cd '.codecept_root_dir().' && ./vendor/bin/doctrine orm:schema-tool:create');
            $cli->seeResultCodeIs(0);

            $this->writeln('Running Doctrine Migrations...');
            $cli->runShellCommand('cd '.codecept_root_dir().' && ./vendor/bin/doctrine orm:schema-tool:update -f');
            //doctrine:migrations:migrate --no-interaction');
            $cli->seeResultCodeIs(0);

            $this->writeln('Seed default data');
            $cli->runShellCommand('cd '.codecept_root_dir().' && php public/console.php app:init');
            $cli->seeResultCodeIs(0);

            $this->writeln('Test database recreated');
        } catch (\Exception $e) {
            $this->writeln(
                sprintf(
                    'An error occurred whilst rebuilding the test database: %s',
                    $e->getMessage()
                )
            );
        }
    }
}
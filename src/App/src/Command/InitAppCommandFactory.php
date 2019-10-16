<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:34
 */

namespace App\Command;

use Interop\Container\ContainerInterface;
use Monolog\Logger;

/**
 * Class CheckFiscalizeCommandFactory
 *
 * @package App\Command
 */
class InitAppCommandFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return InitAppCommand
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new InitAppCommand(
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default')
        );
    }
}

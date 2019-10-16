<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.08.18
 * Time: 11:11
 */

namespace Cms\Command;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Office\Service\Umka;

/**
 * Class GetAllFnCommandFactory
 *
 * @package Cms\Command
 */
class GetAllFnCommandFactory
{

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        return new $requestedName(
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Umka::class)
        );
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:34
 */

namespace ApiV1\Command;

use ApiV1\Service\Umka\UmkaApi;
use Interop\Container\ContainerInterface;
use Monolog\Logger;

/**
 * Class ApiCommandFactory
 *
 * @package ApiV1\Command
 */
class ApiCommandFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestName
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestName)
    {
        return new $requestName(
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(UmkaApi::class)
        );
    }
}

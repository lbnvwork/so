<?php

namespace ApiInsales\Handler;

use Interop\Container\ContainerInterface;

/**
 * Class UninstallHandlerFactory
 *
 * @package ApiInsales\Handler
 */
class UninstallHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return UninstallHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        return new UninstallHandler($em);
    }
}

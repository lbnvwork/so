<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.04.18
 * Time: 19:32
 */

namespace Office\Action\Api;

use Interop\Container\ContainerInterface;
use Office\Service\Umka;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ApiFactory
 * @package Office\Action\Api
 */
class ApiFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName($container->get('doctrine.entity_manager.orm_default'), $container->get(Umka::class));
    }
}

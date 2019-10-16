<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 8:16
 */

namespace ApiAtolV1\Action;

use ApiV1\Service\Umka\UmkaApi;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ApiFactory
 * @package ApiAtolV1\Action
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
        return new $requestedName($container->get('doctrine.entity_manager.orm_default'), $container->get(UmkaApi::class));
    }
}

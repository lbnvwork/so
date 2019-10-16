<?php
declare(strict_types=1);

namespace Permission\Helper;

use Auth\Service\AuthenticationService;
use Interop\Container\ContainerInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class IsGrantedFactory
 *
 * @package Permission\Helper
 */
class IsGrantedFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return object|IsGranted
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IsGranted($container->get(Rbac::class), $container->get(AuthenticationService::class));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.01.18
 * Time: 10:36
 */

namespace Permission\Middleware;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Exception;
use Zend\Permissions\Rbac\Rbac;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PermissionMiddlewareFactory
 *
 * @package Permission\Middleware
 */
class PermissionMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return PermissionMiddleware
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ServiceManager $container */

        $config = $container->get('config');
        if (!isset($config['rbac']['roles'])) {
            throw new Exception('Rbac roles are not configured');
        }
        if (!isset($config['rbac']['permissions'])) {
            throw new Exception('Rbac permissions are not configured');
        }

        $rbac = new Rbac();
        $rbac->setCreateMissingRoles(true);

        // roles and parents
        foreach ($config['rbac']['roles'] as $role => $parents) {
            $rbac->addRole($role, $parents);
        }

        // permissions
        foreach ($config['rbac']['permissions'] as $role => $permissions) {
            foreach ($permissions as $perm) {
                $rbac->getRole($role)->addPermission($perm);
            }
        }
        $container->setService(Rbac::class, $rbac);

        return new PermissionMiddleware($rbac, $container->get(UrlHelper::class), $config['rbac']['asserts']);
    }
}

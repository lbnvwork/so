<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:56
 */
declare(strict_types=1);

namespace Auth\Action;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class LogoutActionFactory
 *
 * @package Auth\Action
 */
class LogoutActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return LogoutAction|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LogoutAction($container->get(UrlHelper::class));
    }
}

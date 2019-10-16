<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:56
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use Auth\Service\SendMail;
use Auth\UserRepository\Database;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class RestoreActionFactory
 *
 * @package Auth\Action
 */
class RestoreActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return RestoreAction|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RestoreAction(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Database::class),
            $container->get(SendMail::class),
            $container->get(UrlHelper::class)
        );
    }
}

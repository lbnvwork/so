<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:34
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class RegisterActionFactory
 *
 * @package Auth\Action
 */
class RegisterActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return RegisterAction|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RegisterAction(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepositoryInterface::class),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(UrlHelper::class)
        );
    }
}

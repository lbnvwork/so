<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 28.03.18
 * Time: 15:12
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use Auth\UserRepository\Database;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UserProfileActionFactory
 *
 * @package Auth\Action
 */
class UserProfileActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ChangePasswordAction|object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

//        $template->addDefaultParam($requestedName::TEMPLATE_NAME, 'layout', 'layout::cms');

        return new UserProfileAction(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Database::class),
            $container->get(UrlHelper::class),
            $template
        );
    }
}

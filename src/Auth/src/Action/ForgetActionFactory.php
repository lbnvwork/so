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
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ForgetActionFactory
 *
 * @package Auth\Action
 */
class ForgetActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ForgetAction|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ForgetAction(
            $container->get(TemplateRendererInterface::class),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(SendMail::class),
            $container->get(UrlHelper::class)
        );
    }
}

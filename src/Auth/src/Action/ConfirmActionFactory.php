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

/**
 * ackage Auth\Action
 */
class ConfirmActionFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return ConfirmAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ConfirmAction($container->get('doctrine.entity_manager.orm_default'), $container->get(UrlHelper::class));
    }
}

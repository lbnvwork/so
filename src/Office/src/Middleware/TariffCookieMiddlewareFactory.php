<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:27
 */

namespace Office\Middleware;

use Interop\Container\ContainerInterface;
use App\Helper\UrlHelper;

/**
 * Class TariffCookieMiddlewareFactory
 *
 * @package Office\Middleware
 */
class TariffCookieMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return TariffCookieMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        $uh = $container->get(UrlHelper::class);

        return new TariffCookieMiddleware($em, $uh);
    }
}

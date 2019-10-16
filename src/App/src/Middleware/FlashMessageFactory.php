<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 10:24
 */
declare(strict_types=1);

namespace App\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class SlimFlashFactory
 *
 * @package App\Middleware
 */
class FlashMessageFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return FlashMessageMiddleware
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new FlashMessageMiddleware($template);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.11.18
 * Time: 11:57
 */

namespace App\Listener;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Class LoggingErrorListenerDelegatorFactory
 *
 * @package App\Listener
 */
class LoggingErrorListenerDelegatorFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     *
     * @return ErrorHandler
     */
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        $listener = new LoggingErrorListener($container->get(LoggerInterface::class));
        $errorHandler = $callback();
        $errorHandler->attachListener($listener);

        return $errorHandler;
    }
}

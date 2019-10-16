<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:34
 */

namespace ApiV1\Middleware;

use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class LoggerMiddlewareFactory
 *
 * @package ApiV1\Command
 */
class LoggerMiddlewareFactory implements FactoryInterface
{
    public const API_V1_NAME = 'ApiV1Logger';
    public const API_ATOL_V1_NAME = 'ApiAtolV1Logger';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return LoggerMiddleware
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $filename = $requestedName == self::API_V1_NAME ? 'api' : 'apiatol';

        $log = new Logger($filename);
        $log->pushHandler(new StreamHandler(ROOT_PATH.'data/'.$filename.'-'.date('Y-m-d').'.log'));

        return new LoggerMiddleware($log);
    }
}

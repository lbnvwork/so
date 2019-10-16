<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Handler;

use ApiInsales\Service\WebhookParserService;
use ApiV1\Service\Check\Normal;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class PrintCheckHandlerFactory
 *
 * @package ApiInsales\Handler
 */
class PrintCheckHandlerFactory
{
    public const API_INSALES_NAME = 'insales';

    /**
     * @param ContainerInterface $container
     *
     * @return PrintCheckHandler
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container)
    {
        $filename = self::API_INSALES_NAME;

        $logger = new Logger($filename);
        $logger->pushHandler(new StreamHandler(ROOT_PATH.'data/'.$filename.'-'.date('Y-m-d').'.log'));
        $em = $container->get('doctrine.entity_manager.orm_default');
        $webhookService = $container->get(WebhookParserService::class);
        $normal = $container->get(Normal::class);
        return new PrintCheckHandler($webhookService, $normal, $em, $logger);
    }
}

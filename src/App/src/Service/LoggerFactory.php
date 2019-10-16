<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.11.18
 * Time: 12:02
 */

namespace App\Service;

use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

/**
 * Class LoggerFactory
 *
 * @package App\Service
 */
class LoggerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return Logger
     */
    public function __invoke(ContainerInterface $container)
    {
        $logDir = ROOT_PATH.'data/logs/site/';
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0777, true) && !is_dir($logDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDir));
            }
            chown(ROOT_PATH.'data/logs', 'www-data');
            chown(ROOT_PATH.'data/logs/site', 'www-data');
        }

        $logRotating = new RotatingFileHandler($logDir);
        $logRotating->setFilenameFormat('{filename}-{date}.log', 'Y-m-d');
        $mail = new NativeMailerHandler('spozdnyakov@keaz.ru', 'Logs from online.schetmash.com', 'noreply@online.schetmash.com');

        return new Logger(
            'main',
            [
                $logRotating,
                $mail
            ]
        );
    }
}

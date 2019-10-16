<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.11.18
 * Time: 11:56
 */

namespace App\Listener;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class LoggingErrorListener
 *
 * @package App\Listener
 */
class LoggingErrorListener
{
    /**
     * Log format for messages:
     * STATUS [METHOD] path: message
     */
    const LOG_FORMAT = '%d [%s] %s: %s';

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Throwable $error
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->logger->error(
            sprintf(
                self::LOG_FORMAT,
                $response->getStatusCode(),
                $request->getMethod(),
                (string)$request->getUri(),
                $error->getMessage()
            )
        );
    }
}

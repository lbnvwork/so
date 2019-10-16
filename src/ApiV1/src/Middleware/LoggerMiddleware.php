<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.08.18
 * Time: 13:06
 */

namespace ApiV1\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LoggerMiddleware
 *
 * @package ApiV1\Middleware
 */
class LoggerMiddleware implements ServerMiddlewareInterface
{
    private $logger;

    /**
     * LoggerMiddleware constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->logger->addDebug(
            'url',
            [
                'path'  => $request->getUri()->getPath(),
                'query' => $request->getUri()->getQuery(),
            ]
        );
        $this->logger->addDebug(
            'request',
            [
                'body'  => $request->getBody()->getContents(),
                'query' => $request->getQueryParams(),
            ]
        );
        $request->getBody()->rewind(); //необходимо делать чтобы следующий вызов ->getContents() сработал правильно

        $response = $delegate->handle($request);

        $this->logger->addDebug('response', ['content' => $response->getBody()->getContents()]);
        $response->getBody()->rewind();

        return $response;
    }
}

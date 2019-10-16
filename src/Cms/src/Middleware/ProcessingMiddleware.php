<?php
declare(strict_types=1);

namespace Cms\Middleware;

use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;

/**
 * Class ProcessingMiddleware
 *
 * @package Cms\Middleware
 */
class ProcessingMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * ProcessingMiddleware constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');
        /** @var Processing $item */
        $item = $this->entityManager->getRepository(Processing::class)->findOneBy(['id' => $id]);
        if ((int)$id === 0) {
            $item = new Processing();
        }

        if (!$item) {
            return (new Response())->withStatus(404);
        }

        return $handler->handle($request->withAttribute(Processing::class, $item));
    }
}

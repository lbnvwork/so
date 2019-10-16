<?php
declare(strict_types=1);

namespace ApiV1\Middleware;

use ApiV1\Action\Code;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ProcessingMiddleware
 *
 * @package ApiV1\Middleware
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
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Processing $processing */
        $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(['id' => $request->getAttribute('processingId')]);
        if ($processing) {
            return $handler->handle($request->withAttribute(Processing::class, $processing));
        }

        $params = $request->getQueryParams();
        if (!empty($params['external_id'])) {
            $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(['externalId' => $params['external_id']]);
            if ($processing) {
                return $handler->handle($request->withAttribute(Processing::class, $processing));
            }
        }

        return new JsonResponse(
            [
                'code'    => Code::INCORRECT_PROCESSING_ID,
                'message' => Code::getMessage(Code::INCORRECT_PROCESSING_ID),
            ]
        );
    }
}

<?php
declare(strict_types=1);

namespace Office\Middleware;

use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class CheckUserKktMiddleware
 *
 * @package Office\Middleware
 */
class CheckUserKktMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CheckUserKktMiddleware constructor.
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
        $isJsonResponse = false;

        if ($request->hasHeader('x-requested-with') && $request->getHeaderLine('x-requested-with') === 'XMLHttpRequest') {
            $isJsonResponse = true;
        }

        $id = $request->getAttribute('id');
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(
            [
                'id'        => $id,
                'isDeleted' => false
            ]
        );
        if ($kkt === null) {
            return $isJsonResponse ? new Response\JsonResponse(['danger' => ['Касса не найдена']]) : (new Response())->withStatus(404);
        }
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        if (!$kkt->getShop()->getCompany()->getUser()->offsetExists($user->getId())) {
            return $isJsonResponse ? new Response\JsonResponse(['danger' => ['Недостаточно прав']]) : (new Response())->withStatus(403);
        }

        return $handler->handle($request->withAttribute(Kkt::class, $kkt));
    }
}

<?php

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class UninstallHandler
 * удаляет приложение insales
 *
 * @package ApiInsales\Handler
 */
class UninstallHandler implements MiddlewareInterface
{
    private $entityManager;

    /**
     * UninstallHandler constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {

        $this->entityManager = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //$user = $request->getAttribute(UserInterface::class);
        $queryParams = $request->getQueryParams();
        $insalesId = $queryParams['insales_id'] ?? null;
        $shopInsales = $queryParams['shop'] ?? null;
        $password = $queryParams['token'] ?? null;
        /** @var InsalesShop $insalesShop */
        $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
            ->findOneBy(
                [
                    'insalesId'     => $insalesId,
                    'password'      => $password,
                    'shopInsales'   => $shopInsales,
                    //          'userSchetmash' => $user,
                ]
            );
        if ($insalesShop instanceof InsalesShop) {
            $this->entityManager->remove($insalesShop);
            $this->entityManager->flush();

            return new Response\EmptyResponse(200);
        }

        return new Response\EmptyResponse(400);
    }
}

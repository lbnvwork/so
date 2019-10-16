<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.04.18
 * Time: 19:30
 */

namespace Office\Action\Api;

use Doctrine\ORM\EntityManager;
use Office\Service\KktService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

/**
 * Class GetkktInfoAction
 *
 * @package Office\Action\Api
 */
class GetkktInfoAction implements ServerMiddlewareInterface
{
    /**
     * @var KktService
     */
    private $kktService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * GetkktInfoAction constructor.
     *
     * @param EntityManager $entityManager
     * @param KktService $kktService
     */
    public function __construct(EntityManager $entityManager, KktService $kktService)
    {
        $this->kktService = $kktService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var Kkt $kkt */
        $kkt = $request->getAttribute(Kkt::class);

        return new TextResponse($this->kktService->getFiscalReport($kkt));
    }
}

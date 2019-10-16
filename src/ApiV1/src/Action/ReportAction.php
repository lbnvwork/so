<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:27
 */

namespace ApiV1\Action;

use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Processing;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ReportAction
 *
 * @package ApiV1\Action
 */
class ReportAction implements ServerMiddlewareInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @var Normal
     */
    private $normal;

    /**
     * @var Correction
     */
    private $correction;

    /**
     * ReportAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Normal $normal
     * @param Correction $correction
     */
    public function __construct(EntityManager $entityManager, Normal $normal, Correction $correction)
    {
        $this->entityManager = $entityManager;
        $this->normal = $normal;
        $this->correction = $correction;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var Processing $processing */
        $processing = $request->getAttribute(Processing::class);
        if ($processing === null) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORRECT_PROCESSING_ID,
                    'message' => Code::getMessage(Code::INCORRECT_PROCESSING_ID),
                ]
            );
        }

        if ($processing->getError()) {
            return new JsonResponse($this->normal->printError($processing));
        }

        if ($processing->getStatus() === Processing::STATUS_ACCEPT) {
            return new JsonResponse(
                [
                    'id'     => $processing->getId(),
                    'status' => 'accept',
                ]
            );
        }
        if ($processing->getStatus() === Processing::STATUS_PREPARE) {
            return new JsonResponse(
                [
                    'id'     => $processing->getId(),
                    'status' => 'processing',
                ]
            );
        }
        if ($processing->getStatus() === Processing::STATUS_SEND_CLIENT) {
            $processing->setStatus(Processing::STATUS_SUCCESS);
            $this->entityManager->persist($processing);
            $this->entityManager->flush();
        }

        return new JsonResponse($this->normal->report($processing));
    }
}

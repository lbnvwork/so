<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:27
 */

namespace ApiAtolV1\Action;

use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Processing;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use ApiV1\Service\Umka;

/**
 * Class ReportAction
 *
 * @package ApiAtolV1\Action
 */
class ReportAction implements ServerMiddlewareInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * ReportAction constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(['id' => $request->getAttribute('processingId')]);

        $response = [
            'uuid'         => null,
            'status'       => 'fail',
            'error'        => null,
            'payload'      => null,
            'timestamp'    => (new \DateTime())->format('d.m.Y H:i:s'),
            'callback_url' => '',
        ];

        if ($processing === null) {
            $response['error'] =
                [
                    'code' => Code::INCORRECT_PROCESSING_ID,
                    'text' => Code::getMessage(Code::INCORRECT_PROCESSING_ID),
                    'type' => 'system',
                ];

            return (new JsonResponse($response))->withStatus(401);
        }

        $response['uuid'] = $processing->getId();
        $response['callback_url'] = $processing->getCallbackUrl();

        if ($processing->getStatus() === 1) {
            $response['error'] =
                [
                    'code' => Code::PROCESSIG,
                    'text' => Code::getMessage(Code::PROCESSIG),
                    'type' => 'system',
                ];

            return new JsonResponse($response);
        }

        if ($processing->getStatus() === 2) {
            $response['error'] =
                [
                    'code' => Code::PROCESSIG,
                    'text' => Code::getMessage(Code::PROCESSIG),
                    'type' => 'system',
                ];

            return new JsonResponse($response);
        }

        if ($processing->getStatus() === 3) {
            $processing->setStatus(4);
            $this->entityManager->persist($processing);
            $this->entityManager->flush();
        }

        $response['status'] = 'done';
        $response['payload'] = Normal::createPayload($processing);

        $response['group_code'] = $processing->getShop()->getId(); //Лучше бы это убрать, но вдруг нужно.
        $response['device_code'] = $processing->getKkt()->getId(); //Лучше бы это убрать, но вдруг нужно.

        return new JsonResponse($response);
    }
}

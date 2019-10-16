<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:27
 */

namespace ApiV1\Action;

use ApiV1\Middleware\CheckRequestMiddleware;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Json\Json;

/**
 * Class RegisterCheckAction
 *
 * @package ApiV1\Action
 */
class RegisterCheckAction implements ServerMiddlewareInterface
{
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
     * RegisterCheckAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Normal $normal
     * @param Correction $correction
     */
    public function __construct(
        EntityManager $entityManager,
        Normal $normal,
        Correction $correction
    ) {
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
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $operation = array_search($request->getAttribute('operation'), CheckRequestMiddleware::ALLOWED_OPERATION);
        /** @var Shop $shop */
        $shop = $request->getAttribute('shop');

        try {
            $json = Json::decode($request->getBody()->getContents(), Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORECT_DATA,
                    'message' => Code::getMessage(Code::INCORECT_DATA).', '.$e->getMessage(),
                ]
            );
        }

        if (!is_array($json) || empty($json['external_id'])) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORECT_DATA,
                    'message' => Code::getMessage(Code::INCORECT_DATA).', не корректный json',
                ]
            );
        }

        /** @var Processing $processing */
        $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(
            [
                'shop'       => $shop,
                'externalId' => $json['external_id'],
            ]
        );
        if ($processing !== null) {
            return new JsonResponse(
                [
                    'id'     => $processing->getId(),
                    'status' => 'accept',
                ]
            );
        }

        $correctionOperations = [
            Processing::OPERATION_SELL_CORRECTION,
            Processing::OPERATION_BUY_CORRECTION,
        ];

        $typeCheck = in_array($operation, $correctionOperations) ? 'correction' : 'normal';

        return new JsonResponse($this->$typeCheck->accept($json, $operation, $shop));
    }
}

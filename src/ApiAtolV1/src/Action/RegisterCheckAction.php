<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:27
 */

namespace ApiAtolV1\Action;

use ApiV1\Middleware\CheckRequestMiddleware;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RegisterCheckAction
 *
 * @package ApiAtolV1\Action
 */
class RegisterCheckAction implements ServerMiddlewareInterface
{
    private const REQUIRED_ITEM_FILEDS = [
        'name',
        'price',
        'quantity',
        'tax',
        'sum',
    ];

    private $entityManager;

    /**
     * RegisterCheckAction constructor.
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
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var Shop $shop */
        $shop = $request->getAttribute('shop');
        /** @var Kkt $kkt */
        $kkt = null;
        /** @var Kkt $item */
        foreach ($shop->getKkt() as $item) {
            if ($item->getIsEnabled() && $item->getIsFiscalized()) {
                $kkt = $item;
                break;
            }
        }

        $datetime = new \DateTime();
        $response = [
            'uuid'  => null,
            'status' => 'fail',
            'error' => null,
            'timestamp' => $datetime->format('d.m.Y H:i:s'),
        ];

        if ($kkt === null) {
            $response['error'] =
                [
                    'code' => Code::NOT_FOUND_KKT,
                    'text' => Code::getMessage(Code::NOT_FOUND_KKT),
                    'type' => 'system',
                ];
            return (new JsonResponse($response))->withStatus(400);
        }

        $json = json_decode($request->getBody()->getContents(), true);

        if (!is_array($json)) {
            $response['error'] =
                [
                    'code' => Code::INCORECT_DATA,
                    'text' => Code::getMessage(Code::INCORECT_DATA).', некорректный json',
                    'type' => 'system',
                ];
                return (new JsonResponse($response))->withStatus(400);
        }

        if (!isset($json['external_id'], $json['receipt']['total']) || empty($json['receipt']['items']) || (int)$json['receipt']['total'] <= 0) {
            $msg = '';
            if (!isset($json['external_id'])) {
                $msg = 'Не найден атрибут `external_id`';
            }

            if (!isset($json['receipt']['total'])) {
                $msg = 'Не найден атрибут `receipt` -> `total`';
            }

            if (empty($json['receipt']['items'])) {
                $msg = 'Пустой массив `items`';
            }
            if ((int)$json['receipt']['total'] <= 0) {
                $msg = 'Значение `total` должно быть больше 0';
            }

            $response['error'] =
            [
                'code' => Code::INCORECT_DATA,
                'text' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
                'type' => 'system',
            ];

            return (new JsonResponse($response))->withStatus(400);
        }

        $total2 = 0;

        foreach ($json['receipt']['items'] as $item) {
            foreach (self::REQUIRED_ITEM_FILEDS as $key) {
                if (!isset($item[$key])) {
                    $response['error'] =
                        [
                            'code' => Code::INCORECT_DATA,
                            'text' => Code::getMessage(Code::INCORECT_DATA).', не найден элемент массива receipt -> items -> '.$key,
                            'type' => 'system',
                        ];
                        return (new JsonResponse($response))->withStatus(400);
                }
            }

            $total2 = $total2 + $item['sum'];
            if ((float)$item['sum'] !== ( (float)$item['price'] * (float)$item['quantity'] )) {
                $total2 = false;
                break;
            }
        }

        if ($total2 === false || $total2 !== (float)$json['receipt']['total']) {
                $response['error'] =
                        [
                            'code' => Code::INCORRECT_SUM,
                            'text' => Code::getMessage(Code::INCORRECT_SUM),
                            'type' => 'driver',
                        ];
                        return (new JsonResponse($response))->withStatus(400);
        }

        /** @var Processing $processing */
        $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(
            [
                'shop'       => $request->getAttribute('shop'),
                'externalId' => $json['external_id']
            ]
        );
        if ($processing !== null) {
            $response['error'] =
                        [
                            'code' => Code::ISSET_EXTERNALID_SHOP,
                            'text' => Code::getMessage(Code::ISSET_EXTERNALID_SHOP),
                            'type' => 'system',
                        ];

            return new JsonResponse($response);
        }

        $processing = new Processing();
        if (isset($json['service']['callback_url'])) {
            $processing->setCallbackUrl($json['service']['callback_url']);
        }

        $operation = array_search($request->getAttribute('operation'), CheckRequestMiddleware::ALLOWED_OPERATION);


        $processing->setRawData(json_encode($json))
            ->setSum($json['receipt']['total'])
            ->setDatetime($datetime)
            ->setStatus(1)
            ->setOperation($operation)
            ->setShop($request->getAttribute('shop'))
            ->setExternalId($json['external_id']);

        $this->entityManager->persist($processing);
        $this->entityManager->flush();

        $response['uuid'] = $processing->getId();
        $response['status'] = 'wait';

        return new JsonResponse($response);
    }
}

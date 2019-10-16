<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:28
 */

namespace ApiAtolV1\Middleware;

use ApiAtolV1\Action\Code;
use ApiV1\Middleware\CheckRequestMiddleware as CheckRequest;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ApiKey;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class CheckRequestMiddleware
 *
 * @package ApiAtolV1\Middleware
 */
class CheckRequestMiddleware implements ServerMiddlewareInterface
{
    private $entityManager;

    /**
     * CheckRequestMiddleware constructor.
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $query = $request->getQueryParams();

        $datetime = new \DateTime();
        $response = [
            'uuid'  => null,
            'status' => 'fail',
            'error' => null,
            'timestamp' => $datetime->format('d.m.Y H:i:s'),
        ];

        if (empty($query['tokenid'])) {
            $response['error'] =
            [
                'code' => Code::INCORRECT_TOKEN,
                'text' => Code::getMessage(Code::INCORRECT_TOKEN),
                'type' => 'system',
            ];
            return (new JsonResponse($response))->withStatus(400);
        }

        $apiKey = $this->entityManager->getRepository(ApiKey::class)->createQueryBuilder('a')
            ->where('a.token = :token and a.dateExpiredToken >= :date')
            ->setParameter('token', $query['tokenid'])
            ->setParameter('date', $datetime->format('Y-m-d H:i:s'))
            ->getQuery()->getOneOrNullResult();
        if ($apiKey === null) {
            $response['error'] =
            [
                'code' => Code::OLD_TOKEN,
                'text' => Code::getMessage(Code::OLD_TOKEN),
                'type' => 'system',
            ];
            return (new JsonResponse($response))->withStatus(401);
        }

        /** @var Shop $shop */
        $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(['id' => $request->getAttribute('shopId')]);
        //TODO сделать проверку на пренадлежность пользователю
        if ($shop === null) {
            $response['error'] =
            [
                'code' => Code::INCORRECT_SHOP,
                'text' => Code::getMessage(Code::INCORRECT_SHOP),
                'type' => 'system',
            ];
            return (new JsonResponse($response))->withStatus(400);
        }

        $operation = $request->getAttribute('operation');
        if ($operation !== null && !in_array($operation, CheckRequest::ALLOWED_OPERATION)) {
            $response['error'] =
            [
                'code' => Code::INCORRECT_OPERATION,
                'text' => Code::getMessage(Code::INCORRECT_OPERATION),
                'type' => 'system',
            ];
            return (new JsonResponse($response))->withStatus(400);
        }

        if ($shop->getKkt()->count() === 0) {
            $response['error'] =
            [
                'code' => Code::NOT_FOUND_KKT,
                'text' => Code::getMessage(Code::NOT_FOUND_KKT),
                'type' => 'system',
            ];
            return (new JsonResponse($response))->withStatus(400);
        }

        return $delegate->handle($request->withAttribute('apiKey', $apiKey)->withAttribute('shop', $shop));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 18:28
 */

namespace ApiV1\Middleware;

use ApiV1\Action\Code;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ApiKey;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class CheckRequestMiddleware
 *
 * @package ApiV1\Middleware
 */
class CheckRequestMiddleware implements ServerMiddlewareInterface
{
    /**
     * Разрешенные операции
     */
    public const ALLOWED_OPERATION = [
        Processing::OPERATION_SELL            => 'sell',
        Processing::OPERATION_SELL_REFUND     => 'sell_refund',
        Processing::OPERATION_SELL_CORRECTION => 'sell_correction',
        Processing::OPERATION_BUY             => 'buy',
        Processing::OPERATION_BUY_REFUND      => 'buy_refund',
        Processing::OPERATION_BUY_CORRECTION  => 'buy_correction',
    ];

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
     * @return ResponseInterface|JsonResponse
     * @throws NonUniqueResultException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $query = $request->getQueryParams();

        if (empty($query['token'])) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORRECT_TOKEN,
                    'message' => Code::getMessage(Code::INCORRECT_TOKEN),
                ]
            );
        }

        $apiKey = $this->entityManager->getRepository(ApiKey::class)->createQueryBuilder('a')
            ->where('a.token = :token and a.dateExpiredToken >= :date')
            ->setParameter('token', $query['token'])
            ->setParameter('date', (new DateTime())->format('Y-m-d H:i:s'))
            ->getQuery()->getOneOrNullResult();
        if ($apiKey === null) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORRECT_TOKEN,
                    'message' => Code::getMessage(Code::USE_OLD_TOKEN),
                ]
            );
        }

        /** @var Shop $shop */
        $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(['id' => $request->getAttribute('shopId')]);
        //TODO сделать проверку на пренадлежность пользователю
        if ($shop === null) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORRECT_SHOP,
                    'message' => Code::getMessage(Code::INCORRECT_SHOP),
                ]
            );
        }

        $operation = $request->getAttribute('operation');
        if ($operation !== null && !in_array($operation, self::ALLOWED_OPERATION)) {
            return new JsonResponse(
                [
                    'code'    => Code::INCORRECT_OPERATION,
                    'message' => Code::getMessage(Code::INCORRECT_OPERATION),
                ]
            );
        }

        $kkt = $this->getActiveKkt($shop);
        if ($kkt === null) {
            return new JsonResponse(
                [
                    'code'    => Code::NOT_FOUND_KKT,
                    'message' => Code::getMessage(Code::NOT_FOUND_KKT),
                ]
            );
        }

        return $delegate->handle(
            $request->withAttribute('apiKey', $apiKey)
                ->withAttribute('shop', $shop)
        );
    }

    /**
     * @param Shop $shop
     *
     * @return Kkt|null
     */
    public function getActiveKkt(Shop $shop): ?Kkt
    {
        /** @var Kkt $item */
        foreach ($shop->getKkt() as $item) {
            if ($item->getIsEnabled() && $item->getIsFiscalized()) {
                return $item;
            }
        }

        return null;
    }
}

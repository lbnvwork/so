<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

declare(strict_types=1);

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\WebhookParserService;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Monolog\Logger;

/**
 * Class PrintCheckHandler
 * Печатает чек
 *
 * @package ApiInsales\Handler
 */
class PrintCheckHandler implements MiddlewareInterface
{
    private $webhookParser;
    private $normal;
    private $entityManager;
    private $logger;

    /**
     * PrintCheckHandler constructor.
     *
     * @param WebhookParserService $webhookParser
     * @param Normal $normal
     * @param EntityManager $entityManager
     * @param Logger $logger
     */
    public function __construct(
        WebhookParserService $webhookParser,
        Normal $normal,
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->webhookParser = $webhookParser;
        $this->normal = $normal;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $xml = $request->getBody()->getContents();
        $this->webhookParser->loadXml($xml);
        if ($this->webhookParser->isStatusPaid()) {
            $orderArr = $this->webhookParser->getOrderArr();
            $insalesId = $this->webhookParser->getInsalesId();
            /** @var InsalesShop $insalesShop */
            $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
                ->findOneBy(
                    [
                        'insalesId' => $insalesId,
                    ]
                );
            /** @var Shop $shop */
            $shop = $insalesShop instanceof InsalesShop ? $insalesShop->getShopSchetmash() : null;
            if (is_array($orderArr) && $shop) {
                $ret = $this->normal->accept($orderArr, Processing::OPERATION_SELL, $shop);
                if (isset($ret['status']) && $ret['status'] === 'accept') {
                    return new Response\EmptyResponse(200);
                } elseif (isset($ret['message']) && isset($ret['code'])) {
                    $this->logger->addError($ret['code'].' : '.$ret['message']);
                }
            }
        }
        return new Response\EmptyResponse(400);
    }
}

<?php
declare(strict_types=1);

namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\WebhookParserService;
use App\Helper\UrlHelper;
use Auth\Entity\User;
use Codeception\Util\Fixtures;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Zend\Json\Json;

class PrintCheckHandlerCest
{
    private const TEST_USER_ID = 2;

    private const TEST_SHOP_ID = 1;

    protected $insalesId;

    protected $shopInsales;

    protected $shopSchetmash;

    protected $userId;

    protected $user;

    public function _before(\FunctionalTester $tester): void
    {
        $entityManager = $tester->getEntityManager();
        $this->user = $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
        $this->insalesId = rand(100000, 999999);
        $this->shopInsales = 'myshop-ww'.rand(1, 99999).'.myinsales.ru';
        $this->userId = rand(100000, 999999);
        $this->shopSchetmash = $tester->grabEntityFromRepository(Shop::class, ['id' => self::TEST_SHOP_ID]);

        //$entityManager->find(Shop::class, self::TEST_SHOP_ID);

        $tester->haveInRepository(
            InsalesShop::class,
            [
                'password'      => 'f669c2c1abb4fe4284bc317da375a83f',
                'insalesId'     => $this->insalesId,
                'shopInsales'   => $this->shopInsales,
                'userId'        => $this->userId,
                'shopSchetmash' => $this->shopSchetmash,
                'userSchetmash' => $this->user,
            ]
        );
    }

    public function successPrintCheck(\FunctionalTester $tester): void
    {
        $xmlString = Fixtures::get('order2');
        $xmlString = str_replace('test-account-id-value', $this->insalesId, $xmlString);
        /** @var UrlHelper $urlHelper */
        $urlHelper = $tester->getService(UrlHelper::class);
        $tester->sendPOST($urlHelper->generate('insales.printcheck'), $xmlString);
        /** @var WebhookParserService $webhookParser */
        $webhookParser = $tester->getService(WebhookParserService::class);
        $webhookParser->loadXml($xmlString);
        $orderArr = $webhookParser->getOrderArr();
        $tester->assertIsArray($orderArr);
        $tester->seeInRepository(
            Processing::class, [
                'rawData'    => Json::encode($orderArr),
                'sum'        => $orderArr['receipt']['total'],
                'status'     => Processing::STATUS_ACCEPT,
                'shop'       => $this->shopSchetmash,
                'externalId' => $orderArr['external_id'],
            ]
        );
        $tester->seeResponseCodeIs(200);
    }
}
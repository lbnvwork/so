<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 03.07.19
 * Time: 15:22
 */

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\InsalesSettingsService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class InstallHandler
 * Устанавливает приложение insales
 * мануал: https://wiki.insales.ru/wiki/%D0%9A%D0%B0%D0%BA_%D0%B8%D0%BD%D1%82%D0%B5%D0%B3%D1%80%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D1%82%D1%8C%D1%81%D1%8F_%D1%81_InSales
 * пример входящего get-запроса: http://myapp.ru/install?shop=test.myinsales.ru&token=token123&insales_id=123
 *
 * @package ApiInsales\Handler
 */
class InstallHandler implements MiddlewareInterface
{
    private $insalesSettingsService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * InstallHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param InsalesSettingsService $insalesSettingsService
     */
    public function __construct(EntityManager $entityManager, InsalesSettingsService $insalesSettingsService)
    {
        $this->insalesSettingsService = $insalesSettingsService;

        $this->entityManager = $entityManager;
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
        $queryParams = $request->getQueryParams();

        $token = $queryParams['token']; //todo проверка на null
        $secretKey = $this->insalesSettingsService->getAppSecretKey();
        $insalesId = $queryParams['insales_id']; //todo проверка на null, на натуральное число, на уникальность в таблице
        $shop = $queryParams['shop']; //todo проверка на null, на доменное имя, уникальность в таблице
        $password = md5($token.$secretKey);

        //$user = $request->getAttribute(UserInterface::class);

        $insalesShop = new InsalesShop();
        $insalesShop->setInsalesId($insalesId);
        $insalesShop->setShopInsales($shop);
        $insalesShop->setPassword($password);
        //$insalesShop->setUserSchetmash($user);

        $this->entityManager->persist($insalesShop);
        $this->entityManager->flush();

        return new Response\EmptyResponse(200);
    }
}

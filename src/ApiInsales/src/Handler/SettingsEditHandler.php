<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 10.07.19
 * Time: 13:25
 */

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class SettingsEditHandler
 * Сохраняет настройки кассы магазина Insales
 *
 * @package ApiInsales\Handler
 */
class SettingsEditHandler implements MiddlewareInterface
{
    private $urlHelper;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SettingsEditHandler constructor.
     *
     * @param EntityManager $em
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $em, UrlHelper $urlHelper)
    {
        $this->entityManager = $em;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $queryParams = $request->getQueryParams();
        /** @var InsalesShop $insalesShop */
        $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
            ->findOneBy(
                [
                    'userId' => $queryParams['user_id'],
                ]
            );
        /** @var InsalesShop $insalesShop */
        $params = $request->getParsedBody();
        /** @var Shop $shop */
        $shop = $this->entityManager->find(Shop::class, (int)$params['shopSchetmash']);
        $shopError = false;
        if ($shop instanceof Shop) {
            /** @var User $user */
            $currUser = $request->getAttribute(UserInterface::class);
            $shopUsers = $shop->getCompany()->getUser()->toArray();
            if (in_array($currUser, $shopUsers)) {
                $insalesShop->setShopSchetmash($shop);
                $this->entityManager->persist($insalesShop);
                $this->entityManager->flush();
                $flashMessage->addSuccessMessage('Настройки успешно сохранены!');
            } else {
                $shopError = true;
            }
        } else {
            $shopError = true;
        }
        if ($shopError) {
            $flashMessage->addErrorMessage('Магазин не найден!');
        }
        return new Response\RedirectResponse($this->urlHelper->generate('insales.settings.view', [], $request->getQueryParams()));
    }
}

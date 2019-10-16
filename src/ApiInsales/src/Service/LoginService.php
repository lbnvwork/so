<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Service;

use ApiInsales\Entity\InsalesShop;
use Doctrine\ORM\EntityManager;
use Office\Entity\Shop;
use Zend\Expressive\Session\LazySession;

/**
 * Class LoginService
 * функции для логина-автологина
 *
 * @package ApiInsales\Service
 */
class LoginService
{
    private $entityManager;

    /**
     * LoginService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param InsalesShop $insalesShop
     * @param LazySession $session
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function authenticate(InsalesShop $insalesShop, LazySession $session)
    {
        if ($insalesShop->getShopSchetmash() !== null) {
            $shopUsers = $insalesShop->getShopSchetmash()->getCompany()->getUser()->toArray();
            foreach ($shopUsers as $shopUser) {
                $session->set('auth_user', $shopUser->getId());
                break;
            }
        }
    }
}

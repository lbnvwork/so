<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace Office\Middleware;

use App\Helper\UrlHelper;
use Office\Entity\Tariff;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\UserInterface;
use Doctrine\ORM\EntityManager;
use Auth\Entity\User;
use Zend\Http\Header\SetCookie;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class TariffCookieService
 *
 * @package Office\Service
 */
class TariffCookieMiddleware implements ServerMiddlewareInterface
{
    private $entityManager;

    private $urlHelper;

    /**
     * TariffCookieMiddleware constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return string|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $setcookie = null;
        $cookieName = 'tariffId';
        if (isset($request->getCookieParams()[$cookieName])) {
            $tariffId = $request->getCookieParams()[$cookieName];
            $tariff = $this->entityManager->getRepository(Tariff::class)
                ->findOneBy(['id' => $tariffId]);
            $user = $request->getAttribute(UserInterface::class)
                ->setTariff($tariff);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $setcookie = new SetCookie($cookieName, '', time() - 100, '/');
        }
        if (is_object($setcookie)) {
            return new RedirectResponse($this->urlHelper->generate('office.company'), 302, ['Set-Cookie' => $setcookie->getFieldValue()]);
        } else {
            return $delegate->handle($request);
        }
    }
}

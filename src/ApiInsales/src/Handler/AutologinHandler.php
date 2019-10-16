<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\LoginService;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Diactoros\Response;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class AutologinHandler
 * Выполняет проверку авторизации в insales
 *
 * @package ApiInsales\Handler
 */
class AutologinHandler implements MiddlewareInterface
{
    private $entityManager;

    private $insalesSettingsService;

    private $urlHelper;

    private $template;

    private $loginService;

    /**
     * AutologinHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param InsalesSettingsService $insalesSettingsService
     * @param UrlHelper $urlHelper
     * @param TemplateRendererInterface $template
     * @param LoginService $loginService
     */
    public function __construct(
        EntityManager $entityManager,
        InsalesSettingsService $insalesSettingsService,
        UrlHelper $urlHelper,
        TemplateRendererInterface $template,
        LoginService $loginService
    ) {

        $this->entityManager = $entityManager;
        $this->insalesSettingsService = $insalesSettingsService;
        $this->urlHelper = $urlHelper;
        $this->template = $template;
        $this->loginService = $loginService;
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
        $queryParams = $request->getQueryParams();
        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $paramsKeys = array_keys($queryParams);
        $token = isset($queryParams['user_id']) ? $session->get($this->insalesSettingsService->getSessionTokenName($queryParams['user_id'])) : null;
        if ($token && array_diff_key($this->insalesSettingsService->getGETAutologinKeys(), $paramsKeys) === []) {
            /** @var InsalesShop $insalesShop */
            $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
                ->findOneBy(
                    [
                        'userId' => $queryParams['user_id'],
                    ]
                );
            if ($insalesShop === null) {
                return new Response\HtmlResponse($this->template->render('error::400'), 400);
            }

            $password = $insalesShop->getPassword();
            //Получение токена для сравнения по формуле MD5(token + user_email + user_name + user_id +email_сonfirmed + password)
            $validToken = md5(
                $token.
                $queryParams['user_email'].
                $queryParams['user_name'].
                $queryParams['user_id'].
                $queryParams['email_confirmed'].
                $password
            );
            //Токены равны?
            if ($queryParams['token3'] === $validToken) {
                //Запись логина в сессию
                $session->set($this->insalesSettingsService->getSessionLoginName($queryParams['user_id']), true);
                //Удаление токена из сессии
                $session->unset($this->insalesSettingsService->getSessionTokenName($queryParams['user_id']));
                //Авторизация
                $this->loginService->authenticate($insalesShop, $session);
                //Редирект на страницу настроек
                return new Response\RedirectResponse(
                    $this->urlHelper->generate(
                        'insales.login',
                        [],
                        [
                            'insales_id' => $insalesShop->getInsalesId(),
                            'shop'       => $insalesShop->getShopInsales(),
                            'user_email' => $queryParams['user_email'],
                            'user_id'    => $queryParams['user_id'],
                        ]
                    )
                );
            }
        }
        return new Response\EmptyResponse(400);
    }
}

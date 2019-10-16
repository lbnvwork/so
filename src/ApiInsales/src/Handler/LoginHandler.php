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
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Diactoros\Response;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class LoginHandler
 * Выполняет проверку логина, направляет либо на страницу настроек, либо на автологин
 *
 * @package ApiInsales\Handler
 */
class LoginHandler implements MiddlewareInterface
{
    private $entityManager;

    private $insalesSettingsService;

    private $urlHelper;

    private $template;

    private $loginService;

    private $config;

    /**
     * LoginHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param InsalesSettingsService $insalesSettingsService
     * @param UrlHelper $urlHelper
     * @param TemplateRendererInterface $template
     * @param LoginService $loginService
     * @param array $config
     */
    public function __construct(
        EntityManager $entityManager,
        InsalesSettingsService $insalesSettingsService,
        UrlHelper $urlHelper,
        TemplateRendererInterface $template,
        LoginService $loginService,
        array $config
    ) {

        $this->entityManager = $entityManager;
        $this->insalesSettingsService = $insalesSettingsService;
        $this->urlHelper = $urlHelper;
        $this->template = $template;
        $this->loginService = $loginService;
        $this->config = $config;
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

        //Есть параметры запроса на shop,insales_id,user_id?
        $paramsKeys = array_keys($queryParams);
        if (array_diff_key($this->insalesSettingsService->getGETLoginKeys(), $paramsKeys) === []) {
            //Получение и проверка сущности insales
            /** @var InsalesShop $insalesShop */
            $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
                ->findOneBy(
                    [
                        'insalesId' => $queryParams['insales_id'],
                        //'shopInsales' => $queryParams['shop'],
                        //у клиента подставляется в GET параметр домен пользователя, при установке подставляется домен insales
                    ]
                );
            if ($insalesShop === null) {
                //return new Response\HtmlResponse($this->template->render('error::403'), 403);
                return new Response\EmptyResponse(400);
            }
            //Проверка и запись user_id в сущность insales
            if ($insalesShop->getUserId() === null) {
                $insalesShop->setUserId($queryParams['user_id']);
                $this->entityManager->persist($insalesShop);
                $this->entityManager->flush();
            }
            $insalesLogin = $this->insalesSettingsService->getSessionLoginName($queryParams['user_id']);
            //Проверка сессии на логин в insales
            /** @var LazySession $session */
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            if ($insalesLogin) {
                if ($insalesShop->getUserSchetmash() === null) {
                    $user = $request->getAttribute(UserInterface::class);
                    if ($user instanceof User) {
                        $insalesShop->setUserSchetmash($user);
                        $this->entityManager->persist($insalesShop);
                        $this->entityManager->flush();
                    } else {
                        return new Response\RedirectResponse($this->urlHelper->generate('login'));
                    }
                }
                //Авторизация
                $this->loginService->authenticate($insalesShop, $session);
                //Редирект на страницу настроек
                return new Response\RedirectResponse(
                    $this->urlHelper->generate(
                        'insales.settings.view',
                        [],
                        [
                            'user_id' => $queryParams['user_id'],
                        ]
                    )
                );
            }

            $token = md5(uniqid());
            /** @var LazySession $session */
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

            //Запись токена в сессию
            $session->set($this->insalesSettingsService->getSessionTokenName($queryParams['user_id']), $token);
            $my_app_key = $this->insalesSettingsService->getAppId();
            $url =
                'http://'.$queryParams['shop'].'/admin/applications/'.$my_app_key.'/login'.
                '?token='.$token.'&login='.'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.autologin');


            return new Response\RedirectResponse($url);
        }

        return new Response\HtmlResponse(
            $this->template->render(
                'error::403',
                [
                    'layout' => 'layout::auth',
                ]
            ),
            403
        );
    }
}

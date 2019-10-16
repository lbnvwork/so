<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 18.07.19
 * Time: 15:05
 */

namespace ApiInsales\Middleware;

use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\WebhookCurlService;
use App\Service\FlashMessage;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class HookMiddleware
 * Проверка и добавление хука на изменение заказа
 *
 * @package ApiInsales\Middleware
 */
class HookMiddleware implements MiddlewareInterface
{
    private $entityManager;

    private $template;

    private $webhookService;

    /**
     * HookMiddleware constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param WebhookCurlService $webhookService
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, WebhookCurlService $webhookService)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->webhookService = $webhookService;
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

        /** @var InsalesShop $insalesShop */
        $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
            ->findOneBy(
                [
                    'userId' => $queryParams['user_id'],
                ]
            );
        if ($insalesShop === null) {
            return new Response\HtmlResponse($this->template->render('error::404'), 404);
        }
        if ($insalesShop->getHookId() === null) {
            $res = $this->webhookService->addWebhook($insalesShop->getShopInsales(), $insalesShop->getPassword());
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $warning = false;
            if (isset($res['httpCode']) && $res['httpCode'] == '201') {
                $hookId = null;
                $hook = new \SimpleXMLElement($res['data']);
                foreach ($hook->id as $id) {
                    $hookId = $id;
                }
                if ($hookId) {
                    $insalesShop->setHookId($hookId);
                    $this->entityManager->persist($insalesShop);
                    $this->entityManager->flush();
                } else {
                    $warning = true;
                }
            } else {
                $warning = true;
            }
            if ($warning) {
                $flashMessage->addWarningMessage('Не удалось получить хук на изменение заказа!');
            }
        }
        return $handler->handle($request->withAttribute(InsalesShop::class, $insalesShop));
    }
}

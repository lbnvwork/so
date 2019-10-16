<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.01.18
 * Time: 10:50
 */

namespace Cms\Action;

use App\Entity\Setting;
use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\UploadedFile;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class SettingsAction
 *
 * @package Cms\Action\Site
 */
class SettingsAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::settings';
    /**
     * Разрешенные форматы к загрузке
     */
    public const ALLOWED_FORMAT = [
        'image/png'  => 'png',
        'image/jpg'  => 'jpg',
        'image/jpeg' => 'jpeg',
        'image/gif'  => 'gif',
    ];

    private $template;
    private $entityManager;
    private $settingService;
    private $urlHelper;

    /**
     * SettingsAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param SettingService $settingService
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, SettingService $settingService, UrlHelper $urlHelper)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
        $this->settingService = $settingService;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse|RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $this->settingService->updateDefaultParams();

        if ($request->getMethod() === 'POST') {
            return $this->saveSettings($request);
        }

        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['settings' => $settings]));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveSettings(ServerRequestInterface $request): RedirectResponse
    {
        $params = $request->getParsedBody();
        /** @var Setting[] $settings */
        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();

        foreach ($params['main'] as $key => $value) {
            $settings[$key]->setValue($value);
        }


        $this->entityManager->flush();

        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $flashMessage->addSuccessMessage('Настройки сохранены');

        return new RedirectResponse($this->urlHelper->generate('admin.settings'));
    }
}

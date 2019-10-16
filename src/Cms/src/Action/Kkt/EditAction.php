<?php


namespace Cms\Action\Kkt;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class EditAction
 *
 * @package Cms\Action\Kkt
 */
class EditAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::kkt/edit';

    private $template;

    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * EditAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $request->getAttribute('id')]);
        if ($kkt === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            $this->saveKkt($kkt, $params);

            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addSuccessMessage('Данные сохранены');

            return new Response\RedirectResponse($this->urlHelper->generate('admin.kkt.edit', ['id' => $kkt->getId()]));
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['kkt' => $kkt]));
    }

    /**
     * @param Kkt $kkt
     * @param array $params
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveKkt(Kkt $kkt, array $params)
    {
        $kkt->setSerialNumber($params['serialNumber'])
            ->setFsNumber($params['fnNumber'])
            ->setRegNumber($params['rnm'])
            ->setFnLiveTime(empty($params['fnLiveTime']) ? null : $params['fnLiveTime'])
            ->setIsEnabled(isset($params['isEnabled']))
            ->setIsFiscalized(isset($params['isFiscalized']));

        $this->entityManager->flush();
    }
}

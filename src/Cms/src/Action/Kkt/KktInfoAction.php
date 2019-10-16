<?php
declare(strict_types=1);

namespace Cms\Action\Kkt;

use ApiV1\Service\Umka\CloseFn\Response as CloseFnResponse;
use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Office\Service\KktService;
use Office\Service\Umka;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;
use Zend\Json\Json;

/**
 * Class KktInfoAction
 *
 * @package Cms\Action\Kkt
 */
class KktInfoAction implements ServerMiddlewareInterface
{
    /**
     * @var KktService
     */
    private $kktService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * KktInfoAction constructor.
     *
     * @param EntityManager $entityManager
     * @param KktService $kktService
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, KktService $kktService, UrlHelper $urlHelper)
    {
        $this->kktService = $kktService;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     * @throws \ApiV1\Service\Umka\CloseFn\InvalidDocumentException
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

        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        if ($request->getAttribute('action') === 'update') {
            $this->update($kkt);
            $flashMessage->addSuccessMessage('Данные кассы обновлены');

            return new Response\RedirectResponse($this->urlHelper->generate('admin.kkt.edit', ['id' => $kkt->getId()]));
        }

        if ($request->getAttribute('action') === 'close-report' && $kkt->getCloseFnRawData()) {
            return new TextResponse((new CloseFnResponse(Json::decode($kkt->getCloseFnRawData(), Json::TYPE_ARRAY)))->print());
        }

        if ($request->getMethod() === 'POST' && $request->getAttribute('action') === 'fiscal') {
            $params = $request->getParsedBody();
            if (!empty($kkt->getRegNumber()) && !empty($params['reason']) && isset(Umka::REASON_IDS[(int)$params['reason']])) {
                $reason = (int)$params['reason'];
                $this->fiscalize($kkt, $reason);

                $flashMessage->addSuccessMessage('Касса отправлена на фискализацию');
            } else {
                $flashMessage->addErrorMessage('Ошибка фискализации');
            }

            return new Response\RedirectResponse($this->urlHelper->generate('admin.kkt.edit', ['id' => $kkt->getId()]));
        }

        return new TextResponse($this->kktService->getFiscalReport($kkt));
    }

    /**
     * @param Kkt $kkt
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(Kkt $kkt)
    {
        $this->kktService->updateKkt($kkt);
        $this->entityManager->flush();
    }

    /**
     * @param Kkt $kkt
     * @param int $idReason
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fiscalize(Kkt $kkt, int $idReason)
    {
        $this->kktService->fiscalize($kkt, $idReason);
        //Наверное надо в сервис вынести сохранение
        $this->entityManager->flush();
    }
}

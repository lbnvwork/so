<?php


namespace Cms\Action\Kkt;

use ApiV1\Service\Umka\UmkaApi;
use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Symfony\Component\VarDumper\VarDumper;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Json\Json;

/**
 * Class CloseFnAction
 *
 * @package Cms\Action\Kkt
 */
class CloseFnAction implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var UmkaApi
     */
    private $umka;

    /**
     * @var UmkaApi
     */
    private $umkaTest;

    /**
     * CloseFnAction constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     * @param UmkaApi $umka
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, UmkaApi $umka)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->umka = $umka;
        $this->umkaTest = clone $umka;
        $this->umkaTest->setHost('office.armax.ru:38088');
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $request->getAttribute('id')]);
        if ($kkt === null) {
            return (new Response())->withStatus(404);
        }

        $umka = $this->umka;
        if ($kkt->getSerialNumber() === '11200001' || $kkt->getSerialNumber() === '17000675') {
            $umka = $this->umkaTest;
        }

        $response = null;
        try {
            $response = $umka->closeFn($kkt->getSerialNumber(), $kkt->getShop()->getCompany()->getInn());
            $flashMessage->addSuccessMessage('ФН закрыт');
            $kkt->setCloseFnRawData(Json::encode($response))
                ->setIsEnabled(false);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $flashMessage->addErrorMessage('Ошибка закрытия ФН');
//            $this->logger->addCritical(
//                $e->getMessage(),
//                [
//                    'kkt'          => $kkt->getSerialNumber(),
//                    'response'     => $response,
//                    'lastResponse' => $umka->getLastResponse(),
//                    'httpCode'     => $umka->getLastHttpCode()
//                ]
//            );
        }

        return new RedirectResponse($this->urlHelper->generate('admin.kkt.edit', ['id' => $kkt->getId()]));
    }
}

<?php

namespace Office\Action\Kkt;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Office\Entity\Tariff;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

/**
 * Class TariffAction
 *
 * @package Office\Action\Kkt
 */
class TariffAction implements ServerMiddlewareInterface
{
    private $template;

    private $entityManager;

    private $urlHelper;

    /**
     * TariffAction constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var Kkt $kkt */
        $kkt = $request->getAttribute(Kkt::class);

        if ($request->getMethod() == 'POST') {
            return $this->setTariff($request, $kkt);
        }

        return $this->getForm($kkt);
    }

    /**
     * @param Kkt $kkt
     *
     * @return HtmlResponse
     * @throws \Exception
     */
    private function getForm(Kkt $kkt)
    {
        /** @var Tariff[] $tariffs */
        $tariffs = $this->entityManager->getRepository(Tariff::class)
            ->createQueryBuilder('t', 't.id')
            ->where('t.isBeginner = 0')
            ->orderBy('t.sort', 'ASC')
            ->getQuery()
            ->getResult();
        $currTariff = $kkt->getTariff();
        $defaultTariff = null;

        /** @var Tariff $tariffItem */
        foreach ($tariffs as $tariffItem) {
            if ($tariffItem->isDefault()) {
                $defaultTariff = $tariffItem;
            }
        }
        $nextTariff = $defaultTariff;
        if (!empty($kkt->getTariffNext())) {
            $nextTariff = $kkt->getTariffNext();
        } else {
            if ($kkt->getTariff()->getMonthLimit()) {
                $limitDate = (new DateTime($kkt->getTariffDateStart()->format('Y-m-d')))
                    ->addMonths($kkt->getTariff()->getMonthLimit() - 1)
                    ->setTime(0, 0);
                $currDate = (new DateTime())->setTime(0, 0);
                if ($limitDate > $currDate) {
                    $nextTariff = $currTariff;
                }
            }
        }

        return new HtmlResponse(
            $this->template->render(
                'office::kkt/kkt-tariff',
                [
                    'kkt'        => $kkt,
                    'tariffs'    => $tariffs,
                    'currTariff' => $currTariff,
                    'nextTariff' => $nextTariff
                ]
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param Kkt $kkt
     *
     * @return Response|Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function setTariff(ServerRequestInterface $request, Kkt $kkt)
    {
        $post = $request->getParsedBody();
        /** @var Tariff $tariff */
        $tariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(
            [
                'id'         => $post['tariffId'],
                'isBeginner' => false
            ]
        );
        if ($tariff === null) {
            return (new Response())->withStatus(404);
        }

        if ($kkt->getTariff() === $tariff) {
            $tariff = null;
        }
        $kkt->setTariffNext($tariff);
        $this->entityManager->flush();

        return new Response\RedirectResponse($this->urlHelper->generate('office.kkt.kkt-tariff', ['id' => $kkt->getId()]));
    }
}

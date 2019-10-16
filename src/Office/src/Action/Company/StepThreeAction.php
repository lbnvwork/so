<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.03.18
 * Time: 19:56
 */

namespace Office\Action\Company;

use App\Entity\Service;
use App\Helper\UrlHelper;
use App\Service\DateTime;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Company;
use Office\Entity\Invoice;
use Office\Entity\InvoiceItem;
use Office\Entity\Kkt;
use Office\Entity\Shop;
use Office\Service\SendMail;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;
use Office\Entity\Tariff;

/**
 * Class StepThreeAction
 *
 * @package Office\Action\Company
 */
class StepThreeAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    private $sendMail;

    /** @var User */
    private $user;

    /**
     * StepThreeAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     * @param SendMail $sendMail
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper, SendMail $sendMail)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->sendMail = $sendMail;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $this->user = $request->getAttribute(UserInterface::class);

        /** @var Company $company */
        $company = $request->getAttribute(Company::class);

        $shopId = $request->getAttribute('shopId');
        if ($shopId !== null) {
            return $this->editShop($request, $shopId, $company);
        }

        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['company' => $company]);

        return new HtmlResponse(
            $this->template->render(
                'office::company/step-three',
                [
                    'company'  => $company,
                    'shops'    => $shops,
                    'editShop' => null,
                ]
            )
        );
    }

    /**
     *  Редактирование магазина
     *
     * @param ServerRequestInterface $request
     * @param int $shopId
     * @param Company $company
     *
     * @return HtmlResponse|Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editShop(ServerRequestInterface $request, int $shopId, Company $company)
    {
        $editShop = $this->entityManager->getRepository(Shop::class)->findOneBy(
            [
                'company' => $company,
                'id'      => $shopId
            ]
        );
        if ($editShop === null) {
            $editShop = new Shop();
            $editShop->setCompany($company);
        }

        /** @var User $user */
        //$user = $request->getAttribute(UserInterface::class);

        if ($request->getMethod() === 'POST' && $this->user->getId() !== User::TEST_USER_ID) {
            return $this->saveShop($editShop, $request);
        }

        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['company' => $company]);
        $tariffs = $this->entityManager->getRepository(Tariff::class)
            ->createQueryBuilder('t', 't.id')
            ->orderBy('t.sort', 'ASC')
            ->getQuery()
            ->getResult();
        /** @var Tariff $tariff */
        foreach ($tariffs as $tariffKey => $tariff) {
            if ($tariff->getIsBeginner()) {
                /** @var Shop $shop */
                foreach ($shops as $shop) {
                    if (\count($shop->getKKt()) > 0) {
                        /** @var Kkt $kkt */
                        foreach ($shop->getKKt() as $kkt) {
                            if (!$kkt->getIsDeleted() || $kkt->getDateExpired()) {
                                unset($tariffs[$tariffKey]);
                                break(2);
                            }
                        }
                    }
                }
            }
        }
        $currTariffId = null;
        if ($editShop->getKkt()->count()) {
            $currTariffId = $editShop->getKkt()[0]->getTariff()->getId();
            if ($editShop->getKkt()[0]->getTariffNext()) {
                $currTariffId = $editShop->getKkt()[0]->getTariffNext()->getId();
            }
        }

        $currTariffId = $this->user->getTariff() ? $this->user->getTariff()->getId() : $currTariffId;
        if (!isset($tariffs[$currTariffId])) {
            $defaultTariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(['isDefault' => true]);
            $currTariffId = $defaultTariff->getId();
        }

        return new HtmlResponse(
            $this->template->render(
                'office::company/step-three',
                [
                    'company'    => $company,
                    'shops'      => $shops,
                    'editShop'   => $editShop,
                    'user'       => $this->user,
                    'tariffs'    => $tariffs,
                    'currTariff' => $currTariffId
                ]
            )
        );
    }

    /**
     * Сохранение данных магазина
     *
     * @param Shop $shop
     * @param ServerRequestInterface $request
     *
     * @return Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveShop(Shop $shop, ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();

        $shop->setTitle($params['name'])
            ->setUrl($params['url'])
            ->setAddress($params['address'])
            ->setIsSecret(isset($params['shfr']))
            ->setKktParams(isset($params['kktParams']) && is_array($params['kktParams']) ? json_encode($params['kktParams']) : '[]');

        $this->entityManager->persist($shop);

        /** @var Tariff $choosedTariff */
        $choosedTariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(['id' => $params['tariffId']]);

        //begin смена выбора тарифа пользователем в личном кабинете
        if (!$this->user->getTariff() || $this->user->getTariff()->getId() !== $choosedTariff->getId()) {
            $this->user->setTariff($choosedTariff);
        }
        //end смена выбора тарифа пользователем в личном кабинете

        if ((int)$params['userKKT'] > 0) {
            $this->addKkt($shop, $request, $params['userKKT'], $choosedTariff);
        }
        if ($shop->getId() === null) {
            $this->sendMail->sendRegisterNewShop($shop);
        }
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $flashMessage->addSuccessMessage('Данные сохранены');

        $this->entityManager->flush();

        return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepThree', ['id' => $shop->getCompany()->getId()]));
    }

    /**
     * Добавление новых ККТ к магазину
     *
     * @param Shop $shop
     * @param ServerRequestInterface $request
     * @param int $countKKT
     * @param Tariff $tariff
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addKkt(Shop $shop, ServerRequestInterface $request, int $countKKT, Tariff $tariff): void
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $invoice = new Invoice();
        $invoice->setCompany($shop->getCompany())
            ->setStatus(0)
            ->setUser($user)
            ->setDate(new DateTime())
            ->setDateUpdate(new DateTime())
            ->setUpdater($user);
        $kkts = [];
        for ($i = 0; $i < $countKKT; $i++) {
            $kkt = new Kkt();
            $kkt->setShop($shop);
            $kkt->setTariff($tariff);
            $this->entityManager->persist($kkt);
            $this->entityManager->flush();
            $kkts[] = $kkt;
        }

        $sum = 0;
        /** @var Service[] $services */
        $services = $tariff->getService();
        foreach ($services as $service) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setInvoice($invoice)
                ->setTitle($service->getName())
                ->setPrice($service->getPrice())
                ->setService($service)
                ->setQuantity($service->getDefaultValue() * $countKKT)
                ->setSum($service->getDefaultValue() * $service->getPrice() * $countKKT);
            $this->entityManager->persist($invoiceItem);
            $sum += $invoiceItem->getSum();
        }

        for ($i = 0; $i < $countKKT; $i++) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setInvoice($invoice)
                ->setTitle('Тариф '.$tariff->getTitle())
                ->setPrice($tariff->getRentCost())
                ->setTariff($tariff)
                ->setQuantity($tariff->getMonthCount())
                ->setSum($tariff->getMonthCount() * $tariff->getRentCost());
            $this->entityManager->persist($invoiceItem);
            $sum += $invoiceItem->getSum();
        }
        $invoice->setSum($sum);
        $this->entityManager->persist($invoice);

        $this->entityManager->flush();

        $flashMessage->addSuccessMessage(
            'Заявка на установку кассы принята. Для дальнейшей работы необходимо оплатить <a href="'.
            $this->urlHelper->generate('office.invoice', ['id' => $invoice->getId()]).'">счет</a>'
        );

        $this->sendMail->sendAddKkt($shop, $countKKT);
    }
}

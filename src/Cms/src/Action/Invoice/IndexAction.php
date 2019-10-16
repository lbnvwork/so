<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:40
 */

namespace Cms\Action\Invoice;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Office\Entity\MoneyHistory;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Invoice;
use Office\Entity\ReferralPayment;
use Office\Service\SendMail;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class IndexAction
 *
 * @package Cms\Action\Invoice
 */
class IndexAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::invoice';
    public const COUNT_ITEMS = 20;

    private $template;

    private $entityManager;

    private $urlHelper;

    private $sendMail;

    /**
     * IndexAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     * @param SendMail $sendMail
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, UrlHelper $urlHelper, SendMail $sendMail)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->sendMail = $sendMail;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse|Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $id = $request->getAttribute('id');
        if ($id !== null) {
            return $this->acceptInvoice($request);
        }
        $sortType = $request->getQueryParams()['sort'] ?? 'date';

        $order = $request->getQueryParams()['order'] ?? 'DESC';
        $orderCheck = in_array(
            $order,
            [
                'ASC',
                'DESC',
            ]
        ) ? $order : 'ASC';

        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'date',
            'sum',
            'dateUpdate',
            'status',
        ];
        $field = in_array($sortType, $params) ? $sortType : 'date';

        $queryParams = $request->getQueryParams();

        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->getRepository(Invoice::class)->createQueryBuilder('i');
        #$qb = $this->entityManager->getRepository(Invoice::class);
        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('admin.invoice'),
        ];

        #$qb->orderBy('i.date', 'DESC');
        $qb->orderBy('i.'.$field, $orderCheck);
        $invoices = $qb->setMaxResults(self::COUNT_ITEMS)->setFirstResult(self::COUNT_ITEMS * ($page - 1))->getQuery()->getResult();


        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType'  => $field,
                    'order'     => $orderType,
                    'chevron'   => $chevron,
                    'invoices'  => $invoices,
                    'paginator' => $paginator,
                ]
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function acceptInvoice(ServerRequestInterface $request): Response\RedirectResponse
    {
        $id = $request->getAttribute('id');
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        /** @var Invoice $invoice */
        $invoice = $this->entityManager->getRepository(Invoice::class)->findOneBy(['id' => $id]);
        if ($invoice !== null) {
            /** @var User $user */
            $user = $request->getAttribute(UserInterface::class);
            if ($invoice->getStatus() == Invoice::DRAFT) {
                $invoice->setStatus(Invoice::ACCEPT)
                    ->setDateAccept(new DateTime())
                    ->setDateUpdate(new DateTime())
                    ->setUpdater($user);

                $company = $invoice->getCompany();
                $company->addBalance($invoice->getSum());
                /** @var User[] $companyUsers */
                $companyUsers = $company->getUser();
                if ($companyUsers) {
                    foreach ($companyUsers as $companyUser) {
                        if ($companyUser->getRoboPromo() === 1) {
                            $this->sendMail->sendPromoStepTwo($companyUser->getEmail());
                            $companyUser->setRoboPromo(2);
                        }
                        if ($companyUser->getUserRoleManager()->offsetExists('office_admin')) {
                            if ($companyUser->getReferral()) {
                                $refferalPayments = new ReferralPayment();
                                $refferalPayments->setCompany($company)
                                    ->setDatetime(new DateTime())
                                    ->setSum(500)
                                    ->setUser($companyUser->getReferral())
                                    ->setFromUser($companyUser);
                                $this->entityManager->persist($refferalPayments);
                                break;
                            }
                        }
                    }
                }

                $history = new MoneyHistory();
                $history->setType(MoneyHistory::TYPE_IN)
                    ->setTitle('Зачисление по счету #'.$invoice->getId())
                    ->setInvoice($invoice)
                    ->setSum($invoice->getSum())
                    ->setDatetime(new \DateTime())
                    ->setCompany($company);

                $this->entityManager->persist($history);
                $this->entityManager->persist($invoice);
                $this->entityManager->persist($company);
                $this->entityManager->flush();
                $flashMessage->addSuccessMessage('Счет #'.$id.' подтвержден');
            } else {
                $flashMessage->addErrorMessage('Счет уже подтвержден');
            }
        } else {
            $flashMessage->addErrorMessage('Счет не найден');
        }

        return new Response\RedirectResponse($this->urlHelper->generate('admin.invoice'));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:20
 */

namespace Office\Action\Billing;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Dompdf\Dompdf;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Invoice;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class InvoiceAction
 *
 * @package Office\Action\Billing
 */
class InvoiceAction implements ServerMiddlewareInterface
{
    public const COUNT_ITEMS = 20;

    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * InvoiceAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->getRepository(Invoice::class)->createQueryBuilder('i');
        $qb->leftJoin('i.company', 'c')
            ->leftJoin('c.user', 'u')
            ->where('u = :user and c.isDeleted = 0')
            ->setParameter('user', $user);

        $id = $request->getAttribute('id');
        if ($id !== null) {
            $invoice = $qb->andWhere('i.id = :id')->setParameter('id', $id)->getQuery()->getOneOrNullResult();
            if ($invoice !== null) {
                return $this->downloadPdf($invoice);
            }

            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addErrorMessage('Счет не найден');

            return new Response\RedirectResponse($this->urlHelper->generate('office.invoice'));
        }

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('office.invoice')
        ];

        $invoices = $qb->setMaxResults(self::COUNT_ITEMS)->setFirstResult(self::COUNT_ITEMS * ($page - 1))->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'office::billing/invoice',
                [
                    'invoices'  => $invoices,
                    'paginator' => $paginator
                ]
            )
        );
    }

    /**
     * @param Invoice $invoice
     *
     * @return Response\EmptyResponse
     */
    public function downloadPdf(Invoice $invoice)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml(
            $this->template->render(
                'pdf::new-invoice',
                [
                    'layout'  => false,
                    'invoice' => $invoice
                ]
            )
        );

        $dompdf->render();
        $dompdf->stream('invoice-'.$invoice->getId().'.pdf');

        return new Response\EmptyResponse();
    }
}

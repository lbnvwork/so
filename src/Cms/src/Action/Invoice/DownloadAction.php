<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:40
 */

namespace Cms\Action\Invoice;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Dompdf\Dompdf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Invoice;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * Class DownloadAction
 *
 * @package Cms\Action\Invoice
 */
class DownloadAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::invoice';
    public const COUNT_ITEMS = 20;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $id = $request->getAttribute('id');
        if ($id !== null) {
            /** @var Invoice $invoice */
            $invoice = $this->entityManager->getRepository(Invoice::class)->find($id);
            if ($invoice !== null) {
                $dompdf = new Dompdf();
                $dompdf->loadHtml(
                    $this->template->render(
                        'pdf::new-invoice',
                        [
                            'layout'  => false,
                            'invoice' => $invoice,
                        ]
                    )
                );

                $dompdf->render();
                $dompdf->stream('invoice-'.$invoice->getId().'.pdf');

                return new Response\EmptyResponse();
            }
        }

        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $flashMessage->addErrorMessage('Счет не найден');

        return new Response\RedirectResponse($this->urlHelper->generate('admin.invoice'));
    }
}

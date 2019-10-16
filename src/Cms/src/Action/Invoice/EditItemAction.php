<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.05.18
 * Time: 21:09
 */

namespace Cms\Action\Invoice;

use App\Entity\Service;
use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Invoice;
use Office\Entity\InvoiceItem;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class EditItemAction
 *
 * @package Cms\Action\Invoice
 */
class EditItemAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::invoice/edit-item';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|Response|HtmlResponse|static
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $id = $request->getAttribute('itemId');
        $action = $request->getAttribute('itemAction');

        /** @var Invoice $invoice */
        $invoice = $request->getAttribute('invoice');

        $invoiceItem = null;
        if ($id == 0) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setInvoice($invoice);
        } else {
            $invoiceItem = $this->entityManager->getRepository(InvoiceItem::class)->findOneBy(
                [
                    'invoice' => $invoice,
                    'id'      => $id,
                ]
            );
        }

        if ($invoiceItem === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $params = $request->getParsedBody();

            /** @var Service $service */
            $service = $this->entityManager->getRepository(Service::class)->find($params['service']);

            $invoiceItem->setTitle($service->getName())
                ->setQuantity($params['quantity'])
                ->setPrice($service->getPrice())
                ->setService($service);
            $invoiceItem->setSum($invoiceItem->getPrice() * $invoiceItem->getQuantity());
            $this->entityManager->persist($invoiceItem);
            $this->entityManager->flush();

            $sum = 0;
            /** @var InvoiceItem $item */
            foreach ($invoice->getItem() as $item) {
                $sum += $item->getSum();
            }
            $invoice->setSum($sum);

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $flashMessage->addSuccessMessage('Данные сохранены');

            return new Response\RedirectResponse($this->urlHelper->generate('admin.invoice.edit', ['id' => $invoice->getId()]));
        }
        if ($request->getMethod() === 'DELETE') {
            $sum = 0;
            /** @var InvoiceItem $item */
            foreach ($invoice->getItem() as $item) {
                if ($item->getId() === $invoiceItem->getId()) {
                    continue;
                }
                $sum += $item->getSum();
            }
            $invoice->setSum($sum);

            $this->entityManager->remove($invoiceItem);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $data = [];
            if ($request->getAttribute('itemAction') === 'remove') {
                $data['url'] = $this->urlHelper->generate('admin.invoice.edit', ['id' => $invoice->getId()]);
            }

            return new Response\JsonResponse($data);
        }

        $services = $this->entityManager->getRepository(Service::class)->findAll();

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'invoice'     => $invoice,
                    'invoiceItem' => $invoiceItem,
                    'services'    => $services,
                ]
            )
        );
    }
}

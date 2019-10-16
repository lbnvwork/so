<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.05.18
 * Time: 21:09
 */

namespace Cms\Action\Invoice;

use App\Service\DateTime;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Cms\Action\AbstractAction;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Company;
use Office\Entity\Invoice;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class EditAction
 *
 * @package Cms\Action\Invoice
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::invoice/edit';

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
        $id = $request->getAttribute('id');

        /** @var Invoice $invoice */
        $invoice = null;
        if ($id == 0) {
            $invoice = new Invoice();
            $invoice->setStatus(0)
                ->setSum(0);
        } else {
            $invoice = $this->entityManager->getRepository(Invoice::class)->findOneBy(
                [
                    'id'     => $id,
                    'status' => 0,
                ]
            );
        }

        if ($invoice === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getAttribute('itemId') !== null) {
            return $delegate->handle($request->withAttribute('invoice', $invoice));
        }

        if ($request->getMethod() === 'POST') {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $params = $request->getParsedBody();

            /** @var Company $company */
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(
                [
                    'id'        => $params['company'],
                    'isDeleted' => false,
                ]
            );
            if ($company !== null) {
                if (empty($params['number']) || $this->entityManager->getRepository(Invoice::class)->findOneBy(['number' => $params['number']]) === null) {
                    /** @var User $user */
//                    $user = $request->getAttribute(UserInterface::class);
                    $user = $company->getUser()->current();
                    $user = $user ? $user : $request->getAttribute(UserInterface::class);
                    $invoice->setNumber(trim($params['number']))
                        ->setCompany($company)
                        ->setUser($user)
                        ->setUpdater($request->getAttribute(UserInterface::class))
                        ->setDate(new DateTime())
                        ->setDateUpdate(new DateTime());

                    $this->entityManager->persist($invoice);
                    $this->entityManager->flush();
                    if ($invoice->getNumber() === '') {
                        $invoice->setNumber($invoice->getId());
                    }
                    $this->entityManager->flush();

                    $flashMessage->addSuccessMessage('Счет сохранен');
                } else {
                    $flashMessage->addErrorMessage('Счет с таким номером уже есть в системе!');
                }
            } else {
                $flashMessage->addErrorMessage('Компания не найдена, возможно удалена');
            }

            return new Response\RedirectResponse($this->urlHelper->generate('admin.invoice.edit', ['id' => (int)$invoice->getId()]));
        }
        if ($request->getMethod() === 'DELETE') {
            $this->entityManager->remove($invoice);
            $this->entityManager->flush();
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addSuccessMessage('Счет удален');

            return new Response\JsonResponse(['url' => $this->urlHelper->generate('admin.invoice')]);
        }

        $companies = $this->entityManager->getRepository(Company::class)->findBy(['isDeleted' => false]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'invoice'   => $invoice,
                    'companies' => $companies,
                ]
            )
        );
    }
}

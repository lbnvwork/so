<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

declare(strict_types=1);

namespace Cms\Action\Processing;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Processing;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class DeleteAction
 *
 * @package Cms\Action\Processing
 */
class DeleteAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::processing/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|Response|HtmlResponse|RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /** @var Processing $item */
        $item = $request->getAttribute(Processing::class);

        $this->entityManager->remove($item);
        $this->entityManager->flush();
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $flashMessage->addSuccessMessage('Чек удален');

        return new RedirectResponse($this->urlHelper->generate('admin.processing'));
    }
}

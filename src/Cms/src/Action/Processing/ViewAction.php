<?php
declare(strict_types=1);

namespace Cms\Action\Processing;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Processing;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ViewAction
 *
 * @package Cms\Action\Processing
 */
class ViewAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::processing/view';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $id = $request->getAttribute('id');
        /** @var Processing $item */
        $item = $this->entityManager->getRepository(Processing::class)->findOneBy(['id' => $id]);
        if ($item === null) {
            return (new Response())->withStatus(404);
        }
        if ($item->getError()) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addMessageNow(FlashMessage::ERROR, $item->getError());
        }

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'item' => $item,
                ]
            )
        );
    }
}

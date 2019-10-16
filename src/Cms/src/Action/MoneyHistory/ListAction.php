<?php
declare(strict_types=1);

namespace Cms\Action\MoneyHistory;

use Cms\Action\AbstractAction;
use Office\Entity\MoneyHistory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\MoneyHistory
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::money-history/list';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $history = $this->entityManager->getRepository(MoneyHistory::class)->findBy([], ['id' => 'DESC']);

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['history' => $history]));
    }
}

<?php
declare(strict_types=1);

namespace Cms\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class IndexPageAction
 *
 * @package Cms\Action
 */
class IndexPageAction extends AbstractAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        return new HtmlResponse($this->template->render('admin::index-page'));
    }
}

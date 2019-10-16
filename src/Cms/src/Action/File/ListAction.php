<?php
declare(strict_types=1);

namespace Cms\Action\File;

use App\Entity\File;
use Cms\Action\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\File
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::file/list';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $files = $this->entityManager->getRepository(File::class)->findAll();

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['files' => $files]));
    }
}

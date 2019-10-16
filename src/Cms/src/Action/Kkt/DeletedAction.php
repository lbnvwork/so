<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action\Kkt;

use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class DeletedAction
 *
 * @package Cms\Action\Kkt
 */
class DeletedAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::kkt/deleted-kkt';

    private $template;

    private $entityManager;

    /**
     * DeletedAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $sortType = $request->getQueryParams()['sort'] ?? 'id';
        $order = $request->getQueryParams()['order'] ?? 'ASC';
        $orderCheck = in_array(
            $order,
            [
            'ASC',
            'DESC'
            ]
        ) ? $order : 'ASC';
        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'dateDeleted'
        ];
        $field = in_array($sortType, $params) ? $sortType : 'id';
        $kkts = $this->entityManager->getRepository(Kkt::class)
            ->findBy(['isDeleted' => true], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order' => $orderType,
                    'chevron' => $chevron,
                    'kkts' => $kkts
                ]
            )
        );
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:04
 */

namespace Cms\Action;

use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class WidgetsAction
 *
 * @package Cms\Action
 */
class WidgetsAction extends AbstractAction
{
    private $user;

    private $companies;

    /**
     * @var array
     */
    private $widgets;

    /**
     * WidgetsAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        parent::__construct($entityManager, $template, $urlHelper);
        $this->widgets = [
            'small'  => [],
            'big'    => [],
            'middle' => [],
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $user = $request->getAttribute(\Zend\Expressive\Authentication\UserInterface::class);

        if ($user->getUserRoleManager()->offsetExists('admin') || $user->getUserRoleManager()->offsetExists('manager')) {
            $innerDate = $this->getAdminWidgets();
            foreach ($innerDate as $arr) {
                $this->getDate($arr);
            }
        } else {
//            $this->user = $user;
//            $this->companies = $user->getCompany();
//
//            $innerDate = $this->getUserWidgets();
//            foreach ($innerDate as $arr) {
//                $this->getDate($arr);
//            }
        }


        return new HtmlResponse($this->template->render('admin::widgets/widgets', ['widgets' => $this->widgets]));
    }

    /**
     * @param $arr
     */
    private function getDate($arr): void
    {
        $data = [];
        $class = '\Cms\Service\Widgets'.$arr['widget'];
        $funct = $arr['funct'];
        if (isset($this->user) || isset($this->companies)) {
            $ec = new $class($this->entityManager, $this->urlHelper, $this->user, $this->companies);
        } else {
            $ec = new $class($this->entityManager, $this->urlHelper);
        }
        $params = $arr['params'] ?? null;
        $data['data'] = $ec->$funct($params);
        $data['layout'] = $arr['layout'] ?? $arr['funct'];
        if ($data['data']) {
            $this->widgets[$arr['position']][] = $data;
        }
    }

    //удалить!!!
    private function getAdminWidgets(): array
    {
        //Потом переделать на запрос в базу данны.
        //$innerDate будет результом запроса.
        //Заголовки виджетов потом надо тоже перести БД
        $innerDate = [];
        $innerDate[] = [
            'widget'   => 'Users',
            'funct'    => 'small',
            'position' => 'small',
            'params'   => ['day' => 30],
        ];

        $innerDate[] = [
            'widget'   => 'Users',
            'funct'    => 'table',
            'position' => 'big',
            'params'   => ['limit' => 8],
        ];

        $innerDate[] = [
            'layout'   => 'table',
            'widget'   => 'Other1',
            'funct'    => 'table',
            'position' => 'middle',
            'params'   => [
                'limit' => 4,
                'day'   => 7,
            ],
        ];

        $innerDate[] = [
            'widget'   => 'Invoice',
            'funct'    => 'small',
            'position' => 'small',
        ];

        $innerDate[] = [
            'widget'   => 'Invoice',
            'funct'    => 'table',
            'position' => 'big',
            'params'   => ['limit' => 8],
        ];

        $innerDate[] = [
            'widget'   => 'Kkt',
            'layout'   => 'progress',
            'funct'    => 'small',
            'position' => 'middle',
        ];

        /*$innerDate[] = [
            'widget'   => 'Kkt',
            'layout'   => 'table',
            'funct'    => 'table',
            'position' => 'big',
            'params'   => [
                'limit'          => 8,
                'day'            => 30,
                'limitOperation' => 210000,
                'month'          => 12,
            ],
        ];*/
//        $innerDate[] = [
//            'widget'   => 'Processing',
//            'layout'   => 'progress',
//            'funct'    => 'middle',
//            'position' => 'middle',
//            'params'   => ['day' => 7],
//        ];

        $innerDate[] = [
            'widget'   => 'Company',
            'funct'    => 'small',
            'position' => 'small',
            'params'   => ['day' => 30],
        ];

        $innerDate[] = [
            'widget'   => 'Fn',
            'funct'    => 'small',
            'position' => 'small',
        ];

        $innerDate[] = [
            'widget'   => 'Referral',
            'funct'    => 'table',
            'position' => 'big',
            'params'   => ['limit' => 8],
        ];


        $innerDate[] = [
            'widget'   => 'Referral',
            'funct'    => 'small',
            'position' => 'small',
            'params'   => ['day' => 30],
        ];

        $innerDate[] = [
            'widget'   => 'Users',
            'funct'    => 'tableStat',
            'layout'   => 'table2',
            'position' => 'big',
        ];
        $innerDate[] = [
            'widget'   => 'Invoice',
            'funct'    => 'tableStat',
            'layout'   => 'table2',
            'position' => 'big',
        ];

        return $innerDate;
    }

    /**
     * @return array
     */
    private function getUserWidgets(): array
    {
        $innerDate = [];
        $innerDate[] = [
            'widget'   => 'ClientInvoice',
            'funct'    => 'table',
            'layout'   => 'table2',
            'position' => 'middle',
            'params'   => ['limit' => 8],
        ];
        $innerDate[] = [
            'widget'   => 'ClientInvoice',
            'funct'    => 'small',
            'position' => 'small',
        ];
        $innerDate[] = [
            'widget'   => 'ClientInvoice',
            'funct'    => 'tariffInfo',
            'position' => 'big',
            'layout'   => 'tariffInfo',
        ];
        $innerDate[] = [
            'widget'   => 'ClientReferral',
            'funct'    => 'table',
            'layout'   => 'table2',
            'position' => 'middle',
            'params'   => ['month' => 4],
        ];

        return $innerDate;
    }
}

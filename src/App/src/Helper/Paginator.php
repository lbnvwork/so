<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 27.02.18
 * Time: 13:53
 */

namespace App\Helper;

use Doctrine\ORM\EntityManager;
use Zend\Expressive\Template;

/**
 * Class Paginator
 *
 * @package App\Helper
 */
class Paginator
{
    public const MAX_PAGES_TO_SHOW = 5;

    protected $entityManager;

    protected $template;

    protected $paginatorOptions = [];

    /**
     * CatalogWidget constructor.
     *
     * @param Template\TemplateRendererInterface $template
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $paginator
     * @param string $template
     *
     * @return string
     */
    public function __invoke(array $paginator, string $template)
    {
        $this->paginatorOptions = $paginator;

        $pagesCount = $this->getPagesCount();

        $buttons[] = $this->createLeftButton();

        if ($pagesCount <= self::MAX_PAGES_TO_SHOW) {
            for ($i = 1; $i <= $pagesCount; $i++) {
                $buttons[] = $this->createPage($i, $i == $this->paginatorOptions['currentPage']);
            }
        } else {
            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int)floor((self::MAX_PAGES_TO_SHOW - 3) / 2);
            if ($this->paginatorOptions['currentPage'] + $numAdjacents > $pagesCount) {
                $slidingStart = $pagesCount - self::MAX_PAGES_TO_SHOW + 2;
            } else {
                $slidingStart = $this->paginatorOptions['currentPage'] - $numAdjacents;
            }
            if ($slidingStart < 2) {
                $slidingStart = 2;
            }
            $slidingEnd = $slidingStart + self::MAX_PAGES_TO_SHOW - 3;
            if ($slidingEnd >= $pagesCount) {
                $slidingEnd = $pagesCount - 1;
            }
            // Build the list of pages.
            $buttons[] = $this->createPage(1, $this->paginatorOptions['currentPage'] == 1);
            if ($slidingStart > 2) {
                $buttons[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $buttons[] = $this->createPage($i, $i == $this->paginatorOptions['currentPage']);
            }
            if ($slidingEnd < $pagesCount - 1) {
                $buttons[] = $this->createPageEllipsis();
            }
            $buttons[] = $this->createPage($pagesCount, $this->paginatorOptions['currentPage'] == $pagesCount);
        }

        $buttons[] = $this->createRightButton();

        return $this->template->render(
            $template,
            [
                'layout'  => false,
                'buttons' => $buttons
            ]
        );
    }

    /**
     * @param array $paginator
     *
     * @return $this
     */
    public function setPaginatorOptions(array $paginator): self
    {
        $this->paginatorOptions = $paginator;
        $pagesCount = $this->getPagesCount();

        if ($this->paginatorOptions['currentPage'] > $pagesCount) {
            $this->paginatorOptions['currentPage'] = $pagesCount;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPagesCount(): int
    {
        return ceil($this->paginatorOptions['countItems'] / $this->paginatorOptions['itemsPerPage']);
    }

    /**
     * @param int $page
     *
     * @return string
     */
    public function getPageUrl(int $page): string
    {
        $query = $this->paginatorOptions['query'];
        $query['page'] = $page;

        return $this->paginatorOptions['url'].'?'.http_build_query($query);
    }

    /**
     * @return array
     */
    public function createLeftButton(): array
    {
        return [
            'num'        => '<i class="fa fa-long-arrow-left"></i>',
            'url'        => $this->paginatorOptions['currentPage'] != 1 ? $this->getPageUrl($this->paginatorOptions['currentPage'] - 1) : '',
            'isCurrent'  => false,
            'isDisabled' => $this->paginatorOptions['currentPage'] == 1
        ];
    }

    /**
     * @return array
     */
    public function createRightButton(): array
    {
        $pagesCount = $this->getPagesCount();

        return [
            'num'        => '<i class="fa fa-long-arrow-right"></i>',
            'url'        => $this->paginatorOptions['currentPage'] != $pagesCount ? $this->getPageUrl($this->paginatorOptions['currentPage'] + 1) : '',
            'isCurrent'  => false,
            'isDisabled' => $this->paginatorOptions['currentPage'] == $pagesCount
        ];
    }

    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     *
     * @return array
     */
    protected function createPage($pageNum, $isCurrent = false): array
    {
        return [
            'num'        => $pageNum,
            'url'        => $this->getPageUrl($pageNum),
            'isCurrent'  => $isCurrent,
            'isDisabled' => $isCurrent
        ];
    }

    /**
     * @return array
     */
    protected function createPageEllipsis(): array
    {
        return [
            'num'        => '...',
            'url'        => null,
            'isCurrent'  => false,
            'isDisabled' => true
        ];
    }
}

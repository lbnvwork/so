<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 08.02.18
 * Time: 13:32
 */

namespace App\Helper;

/**
 * Class UrlHelper
 *
 * @package App\Helper
 */
class UrlHelper extends \Zend\Expressive\Helper\UrlHelper
{
    /**
     * @param null $routeName
     * @param array $routeParams
     * @param array $queryParams
     * @param null $fragmentIdentifier
     * @param array $options
     *
     * @return string
     */
//    public function __invoke(
//        $routeName = null,
//        array $routeParams = [],
//        array $queryParams = [],
//        $fragmentIdentifier = null,
//        array $options = []
//    ) {
//        //Что за дебил придумал этот параметр??
//        return parent::__invoke($routeName, $routeParams, $queryParams, $fragmentIdentifier, ['reuse_result_params' => false]);
//    }
}

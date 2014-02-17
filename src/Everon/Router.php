<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon;

use Everon\Config\Interfaces\ItemRouter;

class Router implements Interfaces\Router
{
    use Dependency\Config;
    use Dependency\RequestValidator;

    use Helper\Asserts\IsArrayKey;
    use Helper\Regex;

    /**
     * @var ItemRouter
     */
    protected $CurrentRoute = null;


    /**
     * @param Interfaces\Config $Config
     * @param Interfaces\RequestValidator $Validator
     */
    public function __construct(Interfaces\Config $Config, Interfaces\RequestValidator $Validator)
    {
        $this->Config = $Config;
        $this->RequestValidator = $Validator;
    }

    /**
     * @param Interfaces\Request $Request
     * @return ItemRouter
     * @throws Exception\RouteNotDefined
     */
    public function getRouteByRequest(Interfaces\Request $Request)
    {
        $DefaultItem = null;
        $Item = null;

        foreach ($this->getConfig()->getItems() as $RouteItem) {
            /**
             * @var ItemRouter $RouteItem
             */
            if ($RouteItem->matchesByPath($Request->getPath())) {
                $Item = $RouteItem;
                break;
            }   
            
            //remember the first item as default
            $DefaultItem = ($Item === null && $DefaultItem === null) ? $RouteItem : $DefaultItem;
        }

        //check for default route
        if ($Request->isEmptyUrl() && $Item === null) {
            $DefaultItem = $this->getConfig()->getDefaultItem() ?: $DefaultItem;
            if ($DefaultItem === null) {
                throw new Exception\RouteNotDefined('Default route does not exist');
            }
            
            $Item = $DefaultItem;
        }

        if ($Item === null) {
            throw new Exception\RouteNotDefined($Request->getPath());
        }
        
        $this->validateAndUpdateRequest($Item, $Request);

        return $Item;
    }

    /**
     * @param ItemRouter $RouteItem
     * @param Interfaces\Request $Request
     */
    public function validateAndUpdateRequest(ItemRouter $RouteItem, Interfaces\Request $Request)
    {
        list($query, $get, $post) = $this->getRequestValidator()->validate($RouteItem, $Request);

        $Request->setQueryCollection(
            array_merge($Request->getQueryCollection()->toArray(), $query)
        );

        $Request->setGetCollection(
            array_merge($Request->getGetCollection()->toArray(), $get)
        );

        $Request->setPostCollection(
            array_merge($Request->getPostCollection()->toArray(), $post)
        );
    }
    
    /**
     * @param $url
     * @return ItemRouter |null
     */
    public function getRouteByUrl($url)
    {
        /**
         * @var $RouteItem ItemRouter
         */
        foreach ($this->getConfig()->getItems() as $RouteItem) {
            if (strcasecmp($RouteItem->getUrl(), $url) === 0) {
                return $RouteItem;
            }
        }

        return null;
    }

    /**
     * @param $route_name
     * @return ItemRouter
     * @throws Exception\Router
     */
    public function getRouteByName($route_name)
    {
        try {
            return $this->getConfig()->getItemByName($route_name);
        }
        catch (\Exception $e) {
            throw new Exception\Router($e);
        }
    }

}
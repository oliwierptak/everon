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

use Everon\Interfaces;

class Environment implements Interfaces\Environment
{

    /**
     * @var array
     */
    protected $resources = [];
    
    /**
     * @var string
     */
    protected $environment;


    function __construct($root, $everon_source_root, $environment, $custom_paths = [])
    {
        $this->environment = $environment;
        
        $this->resources = [
            'root' => $root,
            'everon_source_root' => $everon_source_root,
            'vendor' => $root.'vendor'.DIRECTORY_SEPARATOR
        ];

        $this->resources += [
            'application' => $this->getRoot().'Application'.DIRECTORY_SEPARATOR,
            'config' => $this->getRoot().'Config'.DIRECTORY_SEPARATOR,
            'config_flavour' => $this->getRoot().'Config'.DIRECTORY_SEPARATOR,
            'data_mapper' => $this->getRoot().'DataMapper'.DIRECTORY_SEPARATOR,
            'domain' => $this->getRoot().'Domain'.DIRECTORY_SEPARATOR,
            'domain_config' => $this->getRoot().'Domain'.DIRECTORY_SEPARATOR,
            'module' => $this->getRoot().'Module'.DIRECTORY_SEPARATOR,
            'tests' => $this->getRoot().'Tests'.DIRECTORY_SEPARATOR,
            'tmp' => $this->getRoot().'Tmp'.DIRECTORY_SEPARATOR,
            'rest' => $this->getRoot().'Rest'.DIRECTORY_SEPARATOR,
            'web' => $this->getRoot().'Web'.DIRECTORY_SEPARATOR,
            'view' => $this->getRoot().'View'.DIRECTORY_SEPARATOR,
        ];

        $this->resources += [
            'everon_config' => $this->getEveronRoot().'Config'.DIRECTORY_SEPARATOR,
            'everon_interface' => $this->getEveronRoot().'Interfaces'.DIRECTORY_SEPARATOR,
            'everon_helper' => $this->getEveronRoot().'Helper'.DIRECTORY_SEPARATOR,
        ];

        $this->resources += [
            'cache' => $this->getTmp().'cache'.DIRECTORY_SEPARATOR,
            'log' => $this->getTmp().'logs'.DIRECTORY_SEPARATOR,
        ];

        $this->resources += [
            'cache_view' => $this->getCache().'view'.DIRECTORY_SEPARATOR,
        ];
        
        $this->resources = array_merge($this->resources, $custom_paths);
    }

    function getApplication()
    {
        return $this->resources['application'];
    }

    function setApplication($application)
    {
        $this->resources['application'] = $application;
    }

    //todo: replace all methods with get('Src.Everon.Lib');
    function getRoot()
    {
        return $this->resources['root'];
    }
    
    function setRoot($root)
    {
        $this->resources['root'] = $root;
    }

    function getEveronRoot()
    {
        return $this->resources['everon_source_root'];
    }

    function setEveronRoot($everon_root)
    {
        $this->resources['everon_source_root'] = $everon_root;
    }
    
    function getConfig()
    {
        return $this->resources['config'];
    }
    
    function setConfig($config)
    {
        $this->resources['config'] = $config;
    }

    function getConfigFlavour()
    {
        return $this->resources['config_flavour'];
    }

    function setConfigFlavour($config_flavour)
    {
        $this->resources['config_flavour'] = $config_flavour;
    }


    function getDomain()
    {
        return $this->resources['domain'];
    }

    function setDomain($domain)
    {
        $this->resources['domain'] = $domain;
    }
    
    function getDomainConfig()
    {
        return $this->resources['domain_config'];
    }

    function setDomainConfig($domain_config)
    {
        $this->resources['domain_config'] = $domain_config;
    }

    function getDataMapper()
    {
        return $this->resources['data_mapper'];
    }
    
    function setDataMapper($data_mapper)
    {
        $this->resources['data_mapper'] = $data_mapper;
    }
    
    function getView()
    {
        return $this->resources['view'];
    }
    
    function setView($view)
    {
        $this->resources['view'] = $view;
    }

    function getTest()
    {
        return $this->resources['tests'];
    }
    
    function setTest($test)
    {
        $this->resources['tests'] = $test;
    }

    function getEveronConfig()
    {
        return $this->resources['everon_config'];
    }
    
    function setEveronLib($everon_config)
    {
        $this->resources['everon_config'] = $everon_config;
    }

    function getEveronInterface()
    {
        return $this->resources['everon_interface'];
    }
    
    function setEveronInterface($everon_interfaces)
    {
        $this->resources['everon_interface'] = $everon_interfaces;
    }
    
    function getEveronHelper()
    {
        return $this->resources['everon_helper'];
    }
    
    function setEveronHelper($everon_helper)
    {
        $this->resources['everon_helper'] = $everon_helper;
    }

    function getTmp()
    {
        return $this->resources['tmp'];
    }
    
    function setTmp($tmp)
    {
        $this->resources['tmp'] = $tmp;
    }

    function getCache()
    {
        return $this->resources['cache'];
    }
    
    function setCache($cache)
    {
        $this->resources['cache'] = $cache;
    }

    function getCacheView()
    {
        return $this->resources['cache_view'];
    }

    function setViewCache($view_cache)
    {
        $this->resources['cache_view'] = $view_cache;
    }

    function getLog()
    {
        return $this->resources['log'];
    }
    
    function setLog($log)
    {
        $this->resources['log'] = $log;
    }

    function getWeb()
    {
        return $this->resources['web'];
    }

    function setWeb($web)
    {
        $this->resources['web'] = $web;
    }
    
    function getModule()
    {
        return $this->resources['module'];
    }
    
    function setModule($module)
    {
        $this->resources['module'] = $module;
    }

    function getRest()
    {
        return $this->resources['rest'];
    }

    function setRest($rest)
    {
        $this->resources['rest'] = $rest;
    }

    function getVendor()
    {
        return $this->resources['vendor'];
    }

    function setVendor($vendor)
    {
        $this->resources['Vendor'] = $vendor;
    }
    
    function toArray()
    {
        return $this->resources;
    }
    
}
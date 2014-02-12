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

use Everon\Exception;
use Everon\Dependency;

abstract class Module implements Interfaces\Module
{
    use Dependency\Config;
    use Dependency\Injection\Factory;
    use Dependency\Injection\ViewManager;
    
    protected $name = null;
    
    protected $directory = null;

    /**
     * @var Interfaces\ConfigItemRouter
     */
    protected $RouteConfig = null;

    /**
     * @var Interfaces\Collection
     */
    protected $ViewCollection = null;
    
    /**
     * @var Interfaces\Collection
     */
    protected $ControllerCollection = null;

    
    /**
     * @param $name
     * @param $module_directory
     * @param Interfaces\Config $Config
     * @param Interfaces\Config $RouterConfig
     */
    public function __construct($name, $module_directory, Interfaces\Config $Config, Interfaces\Config $RouterConfig)
    {
        $this->name = $name;
        $this->directory = $module_directory;
        $this->Config = $Config;
        $this->RouteConfig = $RouterConfig;
        $this->ViewCollection = new Helper\Collection([]);
        $this->ControllerCollection = new Helper\Collection([]);
    }

    /**
     * @param $name
     * @return Interfaces\Controller
     */
    protected function createController($name)
    {
        return $this->getFactory()->buildController($name, $this, 'Everon\Module\\'.$this->getName().'\Controller');
    }

    /**
     * @param $name
     * @return Interfaces\View
     */
    protected function createView($name)
    {
        $template_directory = $this->getDirectory().'View'.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
        return $this->getViewManager()->createView($name, $template_directory, 'Everon\Module\\'.$this->getName().'\View');
    }

    /**
     * @inheritdoc
     */
    public function getController($name)
    {
        if ($this->ControllerCollection->has($name) === false) {
            $View = $this->createController($name);
            $this->ControllerCollection->set($name, $View);
        }

        return $this->ControllerCollection->get($name);
    }

    /**
     * @inheritdoc
     */
    public function getView($name)
    {
        if ($this->ViewCollection->has($name) === false) {
            $View = $this->createView($name);
            $this->ViewCollection->set($name, $View);
        }
        
        return $this->ViewCollection->get($name);
    }

    /**
     * @inheritdoc
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }
    
    /**
     * @inheritdoc
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @inheritdoc
     */
    public function setRouteConfig(Interfaces\Config $RouteConfig)
    {
        $this->RouteConfig = $RouteConfig;
    }

    /**
     * @inheritdoc
     */
    public function getRouteConfig()
    {
        return $this->RouteConfig;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }
}
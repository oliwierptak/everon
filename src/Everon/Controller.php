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

use Everon\Event;

abstract class Controller implements Interfaces\Controller
{
    use Dependency\Injection\ConfigManager;
    use Dependency\Injection\Logger;
    use Dependency\Injection\Response;
    use Dependency\Injection\Request;
    use Event\Dependency\Injection\EventManager;
    use Module\Dependency\Injection\ModuleManager;

    use Helper\IsCallable;
    use Helper\ToString;
    use Helper\String\LastTokenToName;

    protected $name = null;
    
    protected $action = null;

    /**
     * @var Interfaces\Module
     */
    protected $Module = null;


    /**
     * @var Config\Interfaces\ItemRouter
     */
    protected $CurrentRoute = null;


    /**
     * @param $action
     * @param $result
     * @return void
     */
    protected abstract function prepareResponse($action, $result);

    protected abstract function response();
    
    
    public function __construct(Interfaces\Module $Module)
    {
        $this->Module = $Module;
    }

    /**
     * @param \Everon\Interfaces\Module $Module
     */
    public function setModule($Module)
    {
        $this->Module = $Module;
    }

    /**
     * @return \Everon\Interfaces\Module
     */
    public function getModule()
    {
        return $this->Module;
    }

    /**
     * @param $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        if ($this->name === null) {
            $this->name = $this->stringLastTokenToName(get_class($this));
        }

        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getUrl($name)
    {
        $route = $this->getConfigManager()->getConfigValue('router.'.$name);
        if ($route === null) {
            throw new Exception\Controller('Invalid router config name: "%s"', $name);
        }

        return $route['url'];
    }

    /**
     * @inheritdoc
     */
    public function setCurrentRoute(Config\Interfaces\ItemRouter $CurrentRoute)
    {
        $this->CurrentRoute = $CurrentRoute;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentRoute()
    {
        return $this->CurrentRoute;
    }

    /**
     * @inheritdoc
     */
    public function execute($action)
    {
        $this->action = $action;
        $event_name = $this->getModule()->getName().'.'.$this->getName().'.'.$action;
        
        if ($this->isCallable($this, $action) === false) {
            throw new Exception\InvalidControllerMethod(
                'Controller: "%s@%s" has no action: "%s" defined', [$this->getModule()->getName(), $this->getName(), $action]
            );
        }
        
        $result = $this->getEventManager()->dispatchBefore($event_name);
        $result = $this->validateActionResponseResult($action, $result, false);

        if ($result) {
            $result = $this->{$action}();
            $result = $this->validateActionResponseResult($action, $result, true);
        }
        
        if ($result) {
            $result = $this->getEventManager()->dispatchAfter($event_name);
            $result = $this->validateActionResponseResult($action, $result, false);
        }
        
        $this->prepareResponse($action, $result);
        $this->response();
        
        return $result;
    }

    /**
     * @param $action
     * @param $result
     * @param $use_on_error
     * @return bool
     * @throws Exception\InvalidControllerResponse
     */
    protected function validateActionResponseResult($action, $result, $use_on_error)
    {
        $result = ($result !== false) ? true : $result;
        $this->getResponse()->setResult($result);
        
        if ($result === false) {
            $result_on_error = null;
            if ($use_on_error) {
                $result_on_error = $this->executeOnError($action);
            }
            
            if ($result_on_error === null) {
                throw new Exception\InvalidControllerResponse(
                    'Invalid controller response for: "%s@%s"', [$this->getName(),$action]
                );
            }
        }
        
        return $result;
    }

    /**
     * Null is returned when no action 'OnError' was found
     * 
     * @param $action
     * @return bool|null
     */
    protected function executeOnError($action)
    {
        $action .= 'OnError';
        if ($this->isCallable($this, $action)) {
            $event_name = $this->getModule()->getName().'.'.$this->getName().'.'.$action;
            $result = $this->getEventManager()->dispatchBefore($event_name);
            $result = ($result !== false) ? true : $result;

            if ($result) {
                $result = $this->{$action}();
                $result = ($result !== false) ? true : $result;
            }

            if ($result) {
                $result = $this->getEventManager()->dispatchAfter($event_name);
            }

            $result = ($result !== false) ? true : $result;
            return $result;
        }
        
        return null;
    }

}
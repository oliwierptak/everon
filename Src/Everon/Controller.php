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


abstract class Controller implements Interfaces\Controller
{
    use Dependency\Injection\Logger;
    use Dependency\Injection\Request;
    use Dependency\Injection\Router;

    use Helper\ToString;
    use Helper\String\LastTokenToName;

    /**
     * Controller's name
     * @var string
     */
    protected $name = null;

    /**
     * @param $name
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
     * @param $result
     * @param Interfaces\Response $Response
     * @return Interfaces\Response
     */
    public function result($result, Interfaces\Response $Response)
    {
        $Response->setResult($result);
        
        if ($result === false) {
            $data = vsprintf('Invalid response for route: "%s"', [$this->getRouter()->getCurrentRoute()->getName()]);
            $Response->setData($data);
        }
        else {
            $Response->setData($this->getView()->getOutput());
        }
    }
    
    public function response()
    {
        $Response->send();
        echo $Response->toText();
    }

    /**
     * @param $action
     * @return mixed
     * @throws Exception\InvalidControllerMethod
     */
    public function execute($action)
    {
        if (method_exists($this, $action) === false) {
            throw new Exception\InvalidControllerMethod(
                'Controller: "%s" has no action: "%s" defined',
                [$this->getName(), $action]
            );
        }

        return $this->{$action}();
    }

}
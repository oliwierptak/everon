<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Mvc;

use Everon\Dependency;
use Everon\Domain;
use Everon\Exception;
use Everon\Interfaces;
use Everon\Helper;
use Everon\Http;
use Everon\Module;
use Everon\Mvc;
use Everon\View;

/**
 * @method Http\Interfaces\Response getResponse()
 * @method Module\Interfaces\Mvc getModule()
 */
abstract class Controller extends \Everon\Controller implements Mvc\Interfaces\Controller
{
    use Dependency\Injection\Factory;
    use Domain\Dependency\Injection\DomainManager;
    use Http\Dependency\Injection\HttpSession;
    use View\Dependency\Injection\ViewManager;

    use Helper\Arrays;

    /**
     * @var string
     */
    protected $view_name = null;

    /**
     * @var string
     */
    protected $layout_name = null;


    /**
     * @param Interfaces\Module $Module
     */
    public function __construct(Interfaces\Module $Module)
    {
        parent::__construct($Module);
        $this->view_name = $this->getName();
        $this->layout_name = $this->getName();
    }
    
    /**
     * @param $action
     * @param $result
     */
    protected function prepareResponse($action, $result)
    {
        if ($result) {
            $this->executeView($this->getView(), $action);
        }
        else {
            $this->executeView($this->getView(), $action.'onError');
        }

        $Layout = $this->getViewManager()->createLayout($this->getLayoutName());
        $data = $this->arrayMergeDefault($Layout->getData(), $this->getView()->getData()); //import view variables into template
        
        if ($result) {
            $ActionTemplate = $this->getView()->getTemplate($action, $data);
            if ($ActionTemplate !== null) {
                $this->getView()->setContainer($ActionTemplate);
                $Layout->set('body', $this->getView());
            }
            else {
                $Layout->setData($data);
            }
        }
        else {
            $Layout->setData($data);
        }

        $this->executeView($Layout, $action);
        
        $this->getViewManager()->compileView($action, $Layout);
        $this->getResponse()->setData($Layout->getContainer()->getCompiledContent());
        
        if ($this->getResponse()->wasStatusSet() === false) {//DRY
            $Ok = new Http\Message\Ok();
            $this->getResponse()->setStatusCode($Ok->getCode());
            $this->getResponse()->setStatusMessage($Ok->getMessage());
        }
    }

    protected function response()
    {
        echo $this->getResponse()->toHtml();
    }
    
    /**
     * @param $action
     * @return bool
     */
    /*
    
    call only view onError actions when error has occurred 
    protected function executeOnError($action)
    {
        $result = parent::executeOnError($action);
        $result_view = $this->executeView($this->getView(), $action.'OnError');
        
        if ($result === false && $result_view === false) {
            return false;
        }

        if ($result === true || $result_view === true) {
            return true;
        }
        
        return null;
    }*/

    /**
     * @param View\Interfaces\View $View
     * @param $action
     * @return bool
     */
    protected function executeView(View\Interfaces\View $View, $action)
    {
        $result = $View->execute($action);
        $result = ($result !== false) ? true : $result;
        return $result;
    }

    /**
     * @param string $view_name
     */
    public function setViewName($view_name)
    {
        $this->view_name = $view_name;
    }

    /**
     * @return string
     */
    public function getViewName()
    {
        return $this->view_name;
    }

    /**
     * @param string $layout_name
     */
    public function setLayoutName($layout_name)
    {
        $this->layout_name = $layout_name;
    }

    /**
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layout_name;
    }
    
    /**
     * @inheritdoc
     */
    public function getView()
    {
        return $this->getModule()->getViewByName($this->getLayoutName(), $this->getViewName());
    }

    /**
     * @inheritdoc
     */
    public function setView(View\Interfaces\View $View)
    {
        $this->getModule()->setViewByViewName($this->getLayoutName(), $View);
    }

    /**
     * @inheritdoc
     */
    public function getActionTemplate()
    {
        return $this->getView()->getTemplate($this->action, $this->getView()->getData());
    }

    /**
     * @inheritdoc
     */
    public function showException(\Exception $Exception)
    {
        $Layout = $this->getViewManager()->createLayout('Error');
        $Layout->set('error', $Exception->getMessage());
        $Layout->execute('show');
        
        $this->getViewManager()->compileView('', $Layout);

        $content = (string) $Layout->getContainer()->getCompiledContent();
        $this->getResponse()->setData($content);

        $this->response();
    }

    public function showValidationErrors()
    {
        /**
         * @var \Everon\View\Interfaces\View $ErrorView
         * @var \Everon\Mvc\Interfaces\Controller $Controller
         */
        $error_view = $this->getConfigManager()->getConfigValue('application.error_handler.view', null);
        $error_form_validation_error_template = $this->getConfigManager()->getConfigValue('application.error_handler.validation_error_template', null);
        
        $ErrorView = $this->getViewManager()->createLayout($error_view);
        $ErrorView->set('validation_errors', $this->getRouter()->getRequestValidator()->getErrors() ?: []);
        $ErrorView->execute('show');
        
        $Tpl = $ErrorView->getTemplate($error_form_validation_error_template, $ErrorView->getData());

        $this->getView()->set('error', $Tpl);
        
        $BadRequest = new Http\Message\BadRequest();
        $this->getResponse()->setStatusCode($BadRequest->getCode());
        $this->getResponse()->setStatusMessage($BadRequest->getMessage());
        
        $this->were_error_handled = true;
    }

    /**
     * @param $name
     * @param $message
     */
    public function addValidationError($name, $message)
    {
        $this->getRouter()->getRequestValidator()->addError($name, $message);
    }

    /**
     * @param $name
     * @param $message
     */
    public function removeValidationError($name, $message)
    {
        $this->getRouter()->getRequestValidator()->removeError($name, $message);
    }

    /**
     * @return bool
     */
    public function hasValidationError()
    {
        return ($this->getRouter()->getRequestValidator()->isValid() === false);
    }

    /**
     * @inheritdoc
     */
    public function redirect($name, $query=[], $get=[])
    {
        $this->is_redirecting = true;
        $url = $this->getUrl($name, $query, $get);
        $this->getResponse()->setHeader('refresh', '1; url='.$url);
    }
}
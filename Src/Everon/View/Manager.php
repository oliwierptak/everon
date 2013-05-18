<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\View;

use Everon\Dependency;
use Everon\Exception;
use Everon\Helper;
use Everon\Interfaces;

abstract class Manager implements Interfaces\ViewManager
{
    use Dependency\Injection\Factory;
    use Helper\String\LastTokenToName;

    /**
     * @var array
     */
    protected $views = null;

    protected $compilers = [];

    protected $view_template_directory = null;


    abstract protected function compileTemplate(Interfaces\TemplateContainer $Template);


    public function __construct(array $compilers, $view_template_directory)
    {
        $this->compilers = $compilers;
        $this->view_template_directory = $view_template_directory;
    }

    /**
     * @param array $compilers
     */
    public function setCompilers(array $compilers)
    {
        $this->compilers = $compilers;
    }

    public function getCompilers()
    {
        return $this->compilers;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception\ViewManager
     */
    public function getView($name)
    {
        if (isset($this->views[$name]) === false) {
            $view_template_directory = $this->view_template_directory.$name.DIRECTORY_SEPARATOR;
            if  ((new \SplFileInfo($view_template_directory))->isDir() === false) {
                throw new Exception\ViewManager('View template directory: "%s" does not exist', $view_template_directory);
            }

            $TemplateCompiler = function(Interfaces\TemplateContainer $Template) {
                $this->compileTemplate($Template);
            };
            
            $this->views[$name] = $this->getFactory()->buildView($name, $view_template_directory, $TemplateCompiler);
        }

        return $this->views[$name];
    }    

}
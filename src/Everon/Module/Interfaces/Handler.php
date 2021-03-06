<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Module\Interfaces;

interface Handler extends \Everon\Config\Interfaces\Dependency\Manager, \Everon\Interfaces\Dependency\Factory, \Everon\Interfaces\Dependency\FileSystem
{
    /**
     * @param $name
     * @return \Everon\Module\Interfaces\Module
     */
    function getModuleByName($name);

    /**
     * @param $name
     * @param \Everon\Module\Interfaces\Module $Module
     */
    function setModuleByName($name, \Everon\Module\Interfaces\Module $Module);

    /**
     * @return array
     */
    function getPathsOfActiveModules();

    /**
     * @param $module_name
     * @return \Everon\Interfaces\FactoryWorker
     */
    function getFactoryWorker($module_name);

    function loadModuleDependencies();
}

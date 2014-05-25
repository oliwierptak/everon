<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\View\Interfaces;

interface Element extends \Everon\Interfaces\Arrayable
{
    /**
     * @param $name
     * @param mixed $data
     */
    function set($name, $data);

    /**
     * @param $name
     * @return mixed|null
     */
    function get($name);    
}
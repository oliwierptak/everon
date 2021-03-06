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

use Everon\Helper;
use Everon\View\Interfaces;


abstract class Element extends Helper\PopoProps implements Interfaces\Element
{
    /**
     * @param array $defaults
     * @param mixed $data
     */
    public function __construct($defaults, $data=null)
    {
        $data = (is_array($data) === false) ? [] : $data;
        $data = array_merge($defaults, $data);

        parent::__construct($data);
    }
}
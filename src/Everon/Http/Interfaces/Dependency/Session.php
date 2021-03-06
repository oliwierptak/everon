<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Http\Interfaces\Dependency;

interface Session
{
    /**
     * @return \Everon\Http\Interfaces\Session
     */
    function getHttpSession();

    /**
     * @param \Everon\Http\Interfaces\Session $HttpSession
     */
    function setHttpSession(\Everon\Http\Interfaces\Session $HttpSession);
}

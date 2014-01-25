<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Domain\Interfaces;

use Everon\Interfaces;

interface Entity extends Interfaces\Arrayable
{
    function isNew();
    function isModified();
    function isPersisted();
    function isDeleted();
    function isPropertyModified($name);
    function getId();
    function getModifiedProperties();
    function getValueByName($name);
    function setValueByName($name, $value);
    function persist();
}
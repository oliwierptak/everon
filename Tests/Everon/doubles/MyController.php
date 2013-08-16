<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Test;

class MyController extends \Everon\Core\Mvc\Controller
{
    public function beforeTestOne()
    {
        $this->getView()->setOutput('before test one');
    }

    public function testOne()
    {
        $this->getView()->setOutput('test one');
    }

    public function afterTestOne()
    {
        $this->getView()->setOutput('after test one');
    }

}
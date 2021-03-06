<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Console;

use Everon\Interfaces;
use Everon\Exception;

abstract class Controller extends \Everon\Controller implements Interfaces\Controller
{
    protected $lines = [];
    

    protected function prepareResponse($action, $result)
    {
        $this->getResponse()->setData(
            implode("\n", $this->lines)
        );
    }
    
    protected function response()
    {
        $this->consoleOutput($this->getResponse()->toText());
    }

    protected function consoleOutput($output)
    {
        echo $output."\n";
    }

    public function help()
    {
        $Config = $this->getConfigManager()->getConfigByName('router');
        $this->lines[] = '';
        $this->lines[] = 'Usage:';
        foreach ($Config->getItems() as $Item) {
            $info = @$Item->toArray()['info'];
            $this->lines[] = '  '.ltrim($Item->getUrl(), '/').' : '.$info;
        }
    }

    /**
     * @inheritdoc
     */
    public function showException(\Exception $Exception)
    {
        $message = $Exception->getMessage();
        $message = trim($message) === '' ? $Exception : $message;
        $this->getResponse()->setResult(false);
        $this->getResponse()->setData('Error: '. $message);
        $this->response();
    }
    
}
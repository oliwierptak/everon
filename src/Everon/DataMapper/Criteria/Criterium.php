<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\DataMapper\Criteria;

use Everon\Helper;
use Everon\DataMapper\Exception;
use Everon\DataMapper\Interfaces;

class Criterium implements Interfaces\Criteria
{
    use Helper\Arrays;
    use Helper\ToArray;
    use Helper\ToString;


    protected $column = null;
    
    protected $operator = '=';
    
    protected $value = null;
    
    protected $glue = null;
    
    
    public function __construct($column, $operator, $value)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }
}
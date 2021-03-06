<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Helper;

use Everon\Exception;
use Everon\Interfaces;
use Everon\Helper;

/**
 * Plain Old PHP Object, data accessible only via public properties, eg. $PopoProps->title, $PopoProps->title = 'title'
 *
 * http://en.wikipedia.org/wiki/POJO
 */
class PopoProps implements Interfaces\Arrayable
{
    use Helper\ToArray;

    /**
     * When set to true, accessing unset property will throw exception
     *
     * @var bool
     */
    protected $strict = false;

    /**
     * @var array
     */
    protected $lookup = null;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->updateLookup();
    }

    protected function updateLookup()
    {
        $this->lookup = array_combine(
            array_keys(array_change_key_case($this->data, \CASE_LOWER)),
            array_keys($this->data)
        );
    }

    public function __get($property)
    {
        $property_to_lookup = mb_strtolower($property);
        if (array_key_exists($property_to_lookup, $this->lookup) === false) {
            if ($this->strict) {
                throw new Exception\Popo('Unknown public property: "%s" in "%s"', [$property, get_called_class()]);
            }

            return null;
        }

        $property = $this->lookup[$property_to_lookup];

        return $this->data[$property];
    }

    public function __set($name, $value)
    {
        $property = mb_strtolower($name);
        $this->data[$property] = $value;
        $this->lookup[$property] = $name;
    }

    public function __call($name, $arguments)
    {
        throw new Exception\Popo('Call by method: "%s" is not allowed in: "%"', [$name, 'PopoProps']);
    }

    public function __sleep()
    {
        //todo: test me xxx
        return ['data', 'strict'];
    }

    public static function __set_state(array $array)
    {
        //todo: test me
        $S = new static($array['data']);
        $S->strict = (bool) $array['strict'];
        return $S;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: dloijenga
 * Date: 12-08-14
 * Time: 13:15
 */
namespace Everon\Rest\Interfaces;

interface FilterOperator extends \Everon\Interfaces\Arrayable
{
    /**
     * @param string $column_glue
     */
    function setColumnGlue($column_glue);

    /**
     * @return string
     */
    function getColumnGlue();
        
    /**
     * @return string
     */
    function getValue();

    /**
     * @param string $column
     */
    function setColumn($column);

    /**
     * @return string
     */
    function getOperator();

    /**
     * @return string
     */
    function getColumn();

    /**
     * @param string $operator
     */
    function setOperator($operator);

    /**
     * @param string $value
     */
    function setValue($value);

    /**
     * @return string
     */
    function getGlue();

    /**
     * @param string $glue
     */
    function setGlue($glue);
}
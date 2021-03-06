<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Config\Interfaces;

interface ItemDomain extends Item
{
    /**
     * @return array
     */
    function getConnections();

    /**
     * @param array $connections
     */
    function setConnections($connections);

    /**
     * @return string
     */
    function getTable();
    
    /**
     * @param string $table
     */
    function setTable($table);

    /**
     * @inheritdoc
     */
    function setTableOriginal($table_original);

    /**
     * @inheritdoc
     */
    function getTableOriginal();
    
    /**
     * @return string
     */
    function getIdField();

    /**
     * @param string $id_field
     */
    function setIdField($id_field);

    /**
     * @param string $type
     */
    function setType($type);

    /**
     * @return string
     */
    function getType();
        
    /**
     * @param array $primary_keys
     */
    function setPrimaryKeys($primary_keys);

    /**
     * @return array
     */
    function getPrimaryKeys();

    /**
     * @param array $foreign_keys
     */
    function setForeignKeys(array $foreign_keys);

    /**
     * @return array
     */
    function getForeignKeys();

    /**
     * @param array $columns
     */
    function setColumns($columns);

    /**
     * @return array
     */
    function getColumns();

    /**
     * @param array $nullable
     */
    function setNullable(array $nullable);

    /**
     * @return array
     */
    function getNullable();

}
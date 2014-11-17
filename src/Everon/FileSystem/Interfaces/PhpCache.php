<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\FileSystem\Interfaces;

use Everon\Exception;

interface PhpCache
{
    /**
     * @return string
     */
    function getCacheDirectory();

    /**
     * @param $cache_directory
     */
    function setCacheDirectory($cache_directory);

    /**
     * @inheritdoc
     */
    function load();

    /**
     * @param \SplFileInfo $CacheFile
     * @return null
     */
    function loadFromCache(\SplFileInfo $CacheFile);

    /**
     * @param $name
     * @param array $cache_data
     * @throws \Everon\Exception\Config
     */
    function saveToCache($name, array $cache_data);
}
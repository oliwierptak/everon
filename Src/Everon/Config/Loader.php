<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Config;

use Everon\Dependency;
use Everon\Exception;
use Everon\Helper;
use Everon\Interfaces;

class Loader implements Interfaces\ConfigLoader
{
    protected $config_directory = null;
    protected $cache_directory = null;

    
    /**
     * @param $config_directory
     * @param $cache_directory
     */
    public function __construct($config_directory, $cache_directory)
    {
        $this->config_directory = $config_directory;
        $this->cache_directory = $cache_directory;
    }
    
    public function getConfigDirectory()
    {
        return $this->config_directory;
    }
    
    public function getCacheDirectory()
    {
        return $this->cache_directory;
    }

    /**
     * @param $filename
     * @return array|null
     */
    public function read($filename)
    {
        return @parse_ini_file($filename, true);
    }

    /**
     * @param \Closure $Compiler
     * @param $use_cache
     * @param $default_config_filename
     * @return array
     */
    public function load(\Closure $Compiler, $use_cache, $default_config_filename)
    {
        /**
         * @var \SplFileInfo $ConfigFile
         * @var \Closure $Compiler
         */
        $list = [];
        $IniFiles = new \GlobIterator($this->getConfigDirectory().'*.ini');
        foreach ($IniFiles as $config_filename => $ConfigFile) {
            if (strcasecmp($ConfigFile->getFilename(), $default_config_filename) === 0) {
                continue; //don't load default config again
            }

            $name = $ConfigFile->getBasename('.ini');
            $CacheFile = new \SplFileInfo($this->getCacheDirectory().$ConfigFile->getBasename().'.php');
            if ($use_cache && $CacheFile->isFile()) {
                $filename = $CacheFile->getPathname();
                $ini_config_data = function() use ($filename, $Compiler) {
                    $cache = null;
                    include($filename);
                    return $cache;
                };
            }
            else {
                $ini_config_data = function() use ($config_filename, $Compiler) {
                    $content = parse_ini_file($config_filename, true);
                    $Compiler($content);
                    return $content;
                };
            }

            $list[$name] = [$config_filename, $ini_config_data];
        }
        
        return $list;
    }
    
    /**
     * @param Interfaces\Config $Config
     * @throws Exception\Config
     */
    public function saveConfigToCache(Interfaces\Config $Config)
    {
        try {
            $cache_filename = $this->cache_directory.pathinfo($Config->getFilename(), PATHINFO_BASENAME).'.php';
            $data = var_export($Config->toArray(), true);
            $h = fopen($cache_filename, 'w+');
            fwrite($h, "<?php \$cache = $data; ");
            fclose($h);
        }
        catch (\Exception $e) {
            throw new Exception\Config('Unable to save config cache file: "%s"', $Config->getFilename(), $e);
        }
    }

}
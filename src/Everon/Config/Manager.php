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

class Manager implements Interfaces\ConfigManager
{
    use Dependency\Injection\Environment;
    use Dependency\Injection\Factory;
    use Dependency\Injection\FileSystem;
    use Dependency\ConfigLoader;

    use Helper\Arrays;
    use Helper\Asserts;
    use Helper\Asserts\IsArrayKey;
    use Helper\IsIterable;


    /**
     * @var array
     */
    protected $configs = null;

    protected $default_config_filename = 'application.ini';
    
    protected $default_config_name = 'application';
    
    protected $ExpressionMatcher = null; 

    /**
     * @var array
     */
    protected $default_config_data = null;
    
    protected $is_caching_enabled = null;


    /**
     * @param Interfaces\ConfigLoader $Loader
     */
    public function __construct(Interfaces\ConfigLoader $Loader)
    {
        $this->ConfigLoader = $Loader;
    }

    /**
     * @inheritdoc
     */
    public function isCachingEnabled()
    {
        if ($this->is_caching_enabled === null) {
            $default_config_data = $this->getDefaultConfigData();
            $this->is_caching_enabled = (bool) $default_config_data['cache']['config_manager'];
            if ($this->is_caching_enabled === null) {
                $this->is_caching_enabled = false;
            }
        }
        return $this->is_caching_enabled;
    }

    /**
     * @param bool $caching_enabled
     */
    public function setIsCachingEnabled($caching_enabled)
    {
        $this->is_caching_enabled = $caching_enabled;
    }
    
    /**
     * @param Interfaces\ConfigLoader $Loader
     * @return array
     */
    protected function getConfigDataFromLoader(Interfaces\ConfigLoader $Loader)
    {
        return $Loader->load((bool) $this->isCachingEnabled());
    }

    /**
     * @param $configs_data
     * @return array
     */
    protected function getAllConfigsDataAndCompiler($configs_data)
    {
        /**
         * @var Interfaces\ConfigLoaderItem $ConfigLoaderItem
         */
        $config_items_data = [];
        foreach ($configs_data as $name => $ConfigLoaderItem) {
            $config_items_data[$name] = $ConfigLoaderItem->toArray();
        }

        //compile expressions in one go
        $Compiler = $this->getExpressionMatcher()->getCompiler($config_items_data, $this->getEnvironmentExpressions());
        $Compiler($config_items_data);

        return [$Compiler, $config_items_data];
    }

    protected function loadAndRegisterAllConfigs()
    {
        $configs_data = $this->getConfigDataFromLoader($this->getConfigLoader());
        list($Compiler, $config_items_data) = $this->getAllConfigsDataAndCompiler($configs_data);

        $ini_config_data = $this->getRouterDataFromModules();
        $RouterLoaderItem = $this->getFactory()->buildConfigLoaderItem('//router.ini', $ini_config_data);

        foreach ($configs_data as $name => $ConfigLoaderItem) {
            $ConfigLoaderItem->setData($config_items_data[$name]);
            $this->loadAndRegisterOneConfig($name, $ConfigLoaderItem, $Compiler);
        }

        $this->loadAndRegisterOneConfig('router', $RouterLoaderItem, $Compiler);
    }

    public function getRouterDataFromModules()
    {
        //gather router data from modules xxx
        $ini_config_data = [];
        $module_list = $this->getFileSystem()->listPathDir('//Module');
        /**
         * @var \DirectoryIterator $Dir
         */
        foreach ($module_list as $Dir) {
            $module_name = $Dir->getBasename();
            $config_filename = $this->getFileSystem()->getRealPath('//Module/'.$module_name.'/Config/router.ini');
            $module_config_data = $this->arrayPrefixKey($module_name.'@', parse_ini_file($config_filename, true));

            foreach ($module_config_data as $section => $data) {
                $module_config_data[$section][Item\Router::PROPERTY_MODULE] = $module_name;
            }
            $ini_config_data = $this->arrayMergeDefault($ini_config_data, $module_config_data);
        }

        return $ini_config_data;
    }

    /**
     * @param $name
     * @param $ConfigLoaderItem
     * @param $Compiler
     */
    protected function loadAndRegisterOneConfig($name, $ConfigLoaderItem, $Compiler)
    {
        if ($this->isRegistered($name) === false) {
            $Config = $this->getFactory()->buildConfig($name, $ConfigLoaderItem, $Compiler);
            $this->register($Config);
        }
    }

    /**
     * @return array
     */
    protected function getDefaultConfigData()
    {
        if ($this->default_config_data !== null) {
            return $this->default_config_data;
        }

        $this->default_config_data = parse_ini_string($this->getDefaults(), true);
        
        $directory = $this->getConfigLoader()->getConfigDirectory();
        $ini = $this->getConfigLoader()->read($directory.$this->default_config_filename);
        if (is_array($ini)) {
            $this->default_config_data = $this->arrayMergeDefault($this->default_config_data, $ini);
        }
        
        return $this->default_config_data;
    }
    
    protected function getDefaults()
    {
        return <<<EOF
; Everon application configuration example

[env]
url = /
url_statc = /assets/
name = everon-dev

[modules]
default = UserLogin
active[] = UserManagement

[theme]
default = Main

[database]
driver = pgsql

[assets]
css = %application.env.url_statc%css/
images = %application.env.url_statc%images/
js = %application.env.url_statc%js/
themes = %application.env.url_statc%themes/

[cache]
config_manager = false
autoloader = false
view = false

[view]
compilers[e] = '.htm'
default_extension = '.htm'

[logger]
enabled = true
rotate = 512             ; KB
format = 'c'             ; todo: implment me
format[trace] = 'U'      ; todo: implment me
EOF;
    }

    /**
     * @inheritdoc
     */
    public function registerByFilename($config_name, $filename)
    {
        $default_data = [];
        /**
         * @var Interfaces\Config $Config
         */
        foreach ($this->getConfigs() as $name => $Config) {
            $default_data[$name] = $Config->toArray();
        }

        $default_data[$config_name] = parse_ini_file($filename, true);
        $Compiler = $this->getExpressionMatcher()->getCompiler($default_data, $this->getEnvironmentExpressions());
        $Compiler($default_data);

        $data = $default_data[$config_name];
        $ConfigLoaderItem = $this->getFactory()->buildConfigLoaderItem($filename, $data);
        $ConfigLoaderItem->setData($data);
        $this->loadAndRegisterOneConfig($config_name, $ConfigLoaderItem, $Compiler);
    }

    /**
     * @inheritdoc
     */
    public function getEnvironmentExpressions()
    {
        $data = $this->getEnvironment()->toArray();
        foreach ($data as $key => $value) {
            $data["%environment.paths.$key%"] = $value;
            unset($data[$key]);
        }

        return $data;
    }
    
    /**
     * @inheritdoc
     */
    public function getExpressionMatcher()
    {
        if ($this->ExpressionMatcher === null) {
            $this->ExpressionMatcher = $this->getFactory()->buildConfigExpressionMatcher();
        }
        
        return $this->ExpressionMatcher;
    }
   
    /**
     * @inheritdoc
     */
    public function register(Interfaces\Config $Config)
    {
        if (isset($this->configs[$Config->getName()])) {
            throw new Exception\Config('Config with name: "%s" already registered', $Config->getName());
        }

        $this->configs[$Config->getName()] = $Config;
        if ($this->isCachingEnabled()) {
            $this->getConfigLoader()->saveConfigToCache($Config);
        }
    }

    /**
     * @inheritdoc
     */
    public function unRegister($name)
    {
        @$this->configs[$name] = null;
        unset($this->configs[$name]);
    }

    /**
     * @inheritdoc
     */
    public function isRegistered($name)
    {
        return isset($this->configs[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getConfigByName($name)
    {
        if (is_null($this->configs)) {
            $this->loadAndRegisterAllConfigs();
        }

        $this->assertIsArrayKey($name, $this->configs, 'Invalid config name: %s', 'Everon\Exception\Config');
        return $this->configs[$name];
    }

    /**
     * @inheritdoc
     */
    public function setConfigByName(Interfaces\Config $Config)
    {
        $this->configs[$Config->getName()] = $Config;
    }

    /**
     * @inheritdoc
     */
    public function getConfigValue($expression, $default=null)
    {
        $tokens = explode('.', $expression);
        $token_count = count($tokens);
        if ($token_count < 2) {
            return null;
        }
        
        if (count($tokens) == 2) { //application.env.url
            array_push($tokens, null);  
        }
        
        list($name, $section, $item) = $tokens;
        $Config = $this->getConfigByName($name);
        if ($item !== null) {
            $Config->go($section);
            return $Config->get($item, $default);
        }
        
        return $Config->get($section, $default);
    }
    
    /**
     * @inheritdoc
     */
    public function getConfigs()
    {
        if (is_null($this->configs)) {
            $this->loadAndRegisterAllConfigs();
        }
        
        return $this->configs;
    }

    /**
     * @inheritdoc
     */
    public function getDatabaseConfig()
    {
        $driver = $this->getConfigValue('application.database.driver', 'pgsql');
        return $this->getConfigByName('database_'.$driver);
    }

}
<?php
/**
 * Abstract WordPress Plug and Play plugin.
 * Simplifies creation of singleton WordPress plugin instances.
 * Becomes vey handy if you have to create multiple plugins, 
 * as it also improves plugins performance by using the same base class for all inherited plugins.
 * 
 * @author Backpack.Studio
 */
if (! class_exists('wpPlugAndPlay')) {

    /**
     * Abstract WP Plugin class.
     * Simplifies creation of singleton WordPress plugin instances.
     * Becomes vey handy if you have to create multiple plugins,
     * as it also improves plugins performance by using the same base class for all inherited plugins.
     *
     * Create folder "frameworks" in your plugin folder and add all your PHP scripts.
     * If your scripts use namespaces, then use appropriate sub-folder names to enable automatic load of used classes.
     * For example class if your class \MyNamespace\MyClass resides on path ./frameworks/MyNamespace/MyClass.php,
     * it's loaded automatically by wpPlugAndPlay, no need to write any line of include statements now.
     *
     * @author Backpack.Studio
     * @version 1.0.5
     */
    abstract class wpPlugAndPlay
    {

        /**
         * Disabled for public access.
         *
         * @ignore
         *
         */
        final protected function __construct()
        {
            ;
        }

        /**
         * All child classes should have this method defined.
         * Method is called at the moment of singular object creation.
         */
        abstract protected function init();

        /**
         * Returns an instance of currents singular object.
         * If you need Code Intelligence for your plugin class, then create simple wrapper for this method.
         *
         * @return \wpPlugAndPlay
         */
        final public static function getInstance()
        {
            static $singletons = array();
            
            $plg_class = self::getClassName();
            
            // Check if plugin class is defined
            if (! isset($singletons[$plg_class])) {
                $singletons[$plg_class] = new $plg_class();
                $singletons[$plg_class]->getSpec();
                $singletons[$plg_class]->init();
            }
            // Return instance
            return $singletons[$plg_class];
        }

        /**
         * Disables cloning of current object.
         *
         * @ignore
         *
         */
        final private function __clone()
        {
            ;
        }

        /**
         * Returns the "Late Static Binding" class name.
         *
         * @return string
         */
        final protected static function getClassName()
        {
            return get_called_class();
        }

        /**
         * Generates and returns string for static call of specified method.
         * For example, "myMethod" for class "\MyNameSpace\MyClass" returns "\MyNameSpace\MyClass::myMethod".
         *
         * @param string $method_name            
         * @return string
         */
        final protected static function getStaticCall($method_name)
        {
            return sprintf('\\%s::%s', self::getClassName(), $method_name);
        }

        /**
         * Returns list of called class methods.
         *
         * @return array
         */
        final public static function getMethods()
        {
            $class = self::getClassName();
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods();
            $child_methods = array();
            if (is_array($methods)) {
                foreach ($methods as $value) {
                    if ($value->class == $class) {
                        $child_methods[] = $value->name;
                    }
                }
            }
            return $child_methods;
        }

        /**
         * Determines whether the called class has specified method defined.
         *
         * @param string $method_name            
         * @return boolean
         */
        final protected static function hasMethod($method_name)
        {
            $methods = self::getMethods();
            return in_array($method_name, $methods);
        }

        /**
         * Returns an object containing runtime specifications of plugin.
         *
         * @return stdClass
         */
        final public static function getSpec()
        {
            static $plg_vars;
            if (empty($plg_vars)) {
                
                // Define readonly variables
                $plg_vars = new stdClass();
                $plg_vars->plugin_class = self::getClassName();
                $reflector = new ReflectionClass($plg_vars->plugin_class);
                $plg_vars->plugin_class_short = $reflector->getShortName();
                $plg_vars->plugin_file = $reflector->getFileName();
                $plg_vars->plugin_dir = dirname($plg_vars->plugin_file) . DIRECTORY_SEPARATOR;
                $plg_vars->plugin_url = plugin_dir_url($plg_vars->plugin_file);
                $plg_vars->plugin_frameworks = $plg_vars->plugin_dir . 'frameworks' . DIRECTORY_SEPARATOR;
                if (! file_exists($plg_vars->plugin_frameworks)) {
                    $plg_vars->plugin_frameworks = null;
                }
                $plg_vars->php_autoloader = array(
                    $plg_vars->plugin_class,
                    'loadClass'
                );
                $plg_vars->php_autoloader_call = implode('::', $plg_vars->php_autoloader);
                $plg_vars->language_dir = self::getPath('language');
                if (! file_exists($plg_vars->language_dir)) {
                    $plg_vars->language_dir = false;
                }
            }
            return $plg_vars;
        }

        /**
         * Returns value of given plugin runtime specification.
         *
         * @param string $var_name            
         * @return mixed
         */
        final public static function getSpecByName($var_name)
        {
            if (! is_string($var_name)) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                error_log(sprintf('PHP Coding: Invalid data type "%s" specified for argument "var_name". String required. Please check your code. in %s on line %s', gettype($var_name), $backtrace[0]['file'], $backtrace[0]['line']));
                return null;
            }
            $vars = self::getSpec();
            if (property_exists($vars, $var_name) && isset($vars->$var_name)) {
                return $vars->$var_name;
            } else {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                error_log(sprintf('PHP Coding: Undefined plugin property "%s" specified for argument "var_name". Please check your code. in %s on line %s', $var_name, $backtrace[0]['file'], $backtrace[0]['line']));
            }
            return null;
        }

        /**
         * Returns containing plugin runtime specifications, metadata and information about extended class.
         *
         * @return string
         */
        final public static function getDebugInfo()
        {
            $ext_reflect = new \ReflectionClass(__CLASS__);
            $ext_methods = $ext_reflect->getMethods();
            foreach ($ext_methods as $key => $value) {
                $ext_methods[$key] = $value->name;
                if ($value->name == 'init' || $value->name == '__construct' || $value->name == '__clone') {
                    unset($ext_methods[$key]);
                }
            }
            sort($ext_methods);
            $info = array(
                self::getClassName() => array(
                    'plugin' => self::getMetadata(),
                    'extends' => array(
                        'class' => __CLASS__,
                        'file' => __FILE__,
                        'methods' => $ext_methods
                    )
                )
            );
            return print_r($info, true);
        }

        /**
         * Determines whether current version of PHP is smaller than given version number.
         * If current PHP version is smaller than given, then returns FALSE, otherwise TRUE.
         *
         * @param string $php_version
         *            Version number.
         * @return boolean If current PHP version is smaller then returns FALSE, otherwise TRUE.
         */
        final public static function isPhpVersionValid($php_version)
        {
            if (version_compare(PHP_VERSION, $php_version, '>=')) {
                return true;
            }
            return false;
        }

        /**
         * Returns an object containing all plugin metadata and specifications.
         * Contains metadata defined in plugin definition file, fro example Name, Description, Version etc.
         * Allows to retrieve plugin metadata even before than WordPress function get_plugin_data becomes available.
         *
         * @see https://codex.wordpress.org/Function_Reference/get_plugin_data get_plugin_data
         * @see https://codex.wordpress.org/Function_Reference/get_file_data get_file_data
         * @return stdClass
         */
        final public static function getMetadata()
        {
            static $plg_vars;
            if (empty($plg_vars)) {
                $plg_vars = self::getSpec();
                $plg_vars->plugin_class_methods = self::getMethods();
                $default_headers = array(
                    'Name' => 'Plugin Name',
                    'PluginURI' => 'Plugin URI',
                    'Version' => 'Version',
                    'Description' => 'Description',
                    'Author' => 'Author',
                    'AuthorURI' => 'Author URI',
                    'TextDomain' => 'Text Domain',
                    'DomainPath' => 'Domain Path',
                    'Network' => 'Network'
                );
                if (function_exists('get_plugin_data')) {
                    $plg_data = get_plugin_data($plg_vars->plugin_file);
                } else {
                    $plg_data = get_file_data($plg_vars->plugin_file, $default_headers);
                }
                if (is_array($plg_data)) {
                    foreach ($plg_data as $key => $value) {
                        $plg_vars->$key = $value;
                    }
                }
            }
            return $plg_vars;
        }

        /**
         * Notice about outdated PHP version displayed near the top of admin pages.
         */
        final public static function showNoticePhpVersion()
        {
            if (is_admin()) {
                $plg_data = self::getMetadata();
                $msg[] = '<div class="updated"><p>';
                $msg[] = sprintf('%s is not hooked!<br>', isset($plg_data->Name) ? 'Plugin <strong>' . $plg_data->Name . '</strong>' : ' Class <strong>' . $plg_data->plugin_class . '</strong>');
                $msg[] = 'Your current PHP version is ' . PHP_VERSION . ', which is lower than required. Please check plugin requirements for more details.';
                $msg[] = '</p></div>';
                echo implode(PHP_EOL, $msg);
            }
        }

        /**
         * Register given function as __autoload() implementation.
         *
         * @see spl_autoload_register
         * @param string|array $autoload_function            
         * @param boolean $throw            
         * @param boolean $prepend            
         * @return boolean
         */
        final public static function registerAutoLoad($autoload_function = null, $throw = false, $prepend = false)
        {
            $default_function = self::getSpec()->php_autoloader;
            $autoload_function = (empty($autoload_function)) ? $default_function : $autoload_function;
            $spl_functions = spl_autoload_functions();
            $spl_functions = is_array($spl_functions) ? $spl_functions : array();
            if (in_array('__autoload', $spl_functions)) {
                spl_autoload_register('__autoload');
            }
            if (! in_array($autoload_function, $spl_functions)) {
                return spl_autoload_register($autoload_function, $throw, $prepend);
            }
            return false;
        }

        /**
         * Loads specified class if available.
         *
         * @param string $class
         *            Class name.
         * @return boolean
         */
        public static function loadClass($class)
        {
            static $path;
            if (empty($path)) {
                $path = self::getSpec()->plugin_frameworks;
            }
            if (! empty($class) && ! empty($path)) {
                $class_file = ('\\' != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php' : DIRECTORY_SEPARATOR . $class . '.php';
                $classpath = $path . ltrim($class_file, '\\/');
                if (is_file($classpath)) {
                    $loaded = require_once ($classpath);
                    return $loaded;
                }
            }
            return false;
        }

        /**
         * Generates proper absooute uri from given relative path.
         * You dont have to worry about actual location of your plugin by using this method.
         * Please nothe, this method does not validate existence of given file/folder.
         *
         * @param string $path
         *            Path relative to the plugin root direcotry.
         * @return string Absolute url.
         */
        final public static function getUri($path = '')
        {
            $path = ltrim($path, '\\/');
            return self::getSpec()->plugin_url . $path;
        }

        /**
         * Generates proper absooute path from given relative path.
         * You dont have to worry about actual location of your plugin by using this method.
         * Please nothe, this method does not validate existence of given file/folder.
         *
         * @param string $path
         *            Path relative to the plugin root direcotry.
         * @return string Absolute path.
         */
        final public static function getPath($path = '')
        {
            $path = ltrim($path, '\\/');
            return self::getSpec()->plugin_dir . $path;
        }

        final public static function getScriptHandle($uri, $type = 'css')
        {
            return strtolower(self::getSpecByName('plugin_class_short') . '_' . str_replace('-', '_', sanitize_key($type . '_' . pathinfo($uri, PATHINFO_FILENAME))));
        }

        /**
         * Ads script and styles into collection.
         * This method is intended for internal usage only
         * Do not use this method directly, use addStyle, addAdminStyle, addScript or addAdminScript instead.
         *
         * @param array $reg_data            
         * @return array
         */
        final protected static function addScripts($reg_data = null)
        {
            static $collection;
            if (! isset($collection)) {
                $collection = array();
            }
            if (! empty($reg_data)) {
                $collection[$reg_data['admin'] ? 'admin' : 'wp'][$reg_data['type']][$reg_data['handle']] = $reg_data;
            }
            return $collection;
        }

        /**
         * Ads back-end script into the scripts que.
         * Makes very easy to add scripts into que at the early stages of WordPress execution.
         *
         * @param string $relative_path
         *            Relative path of the script. Relative to the plugin root directory.
         *            Default empty.
         * @param array $dependency
         *            Optional. An array of registered script handles this script depends on.
         *            Default empty array.
         * @param boolean $footer
         *            Optional. Whether to enqueue the script before </body> instead of in the <head>.
         *            Default 'false'.
         */
        final protected static function addAdminScript($relative_path, $dependency = array(), $footer = false)
        {
            $uri = self::getUri($relative_path);
            $reg_data = array(
                'handle' => self::getScriptHandle($uri, 'script'),
                'uri' => $uri,
                'dependency' => $dependency,
                'footer' => $footer,
                'type' => 'script',
                'admin' => true
            );
            self::addScripts($reg_data);
        }

        /**
         * Ads back-end css file into the styles que.
         * Makes very easy to add styles into que at the early stages of WordPress execution.
         *
         * @param string $relative_path
         *            Relative path of the style. Relative to the plugin root directory.
         *            Default empty.
         * @param array $dependency
         *            Optional. An array of registered stylesheet handles this stylesheet depends on.
         *            Default empty array.
         * @param string $media
         *            Optional. The media for which this stylesheet has been defined.
         *            Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
         *            '(orientation: portrait)' and '(max-width: 640px)'.
         */
        final protected static function addAdminStyle($relative_path, $dependency = array(), $media = 'all')
        {
            $uri = self::getUri($relative_path);
            $reg_data = array(
                'handle' => self::getScriptHandle($uri),
                'uri' => $uri,
                'dependency' => $dependency,
                'media' => $media,
                'type' => 'style',
                'admin' => true
            );
            self::addScripts($reg_data);
        }

        /**
         * Ads front-end script into the scripts que.
         * Makes very easy to add scripts into que at the early stages of WordPress execution.
         *
         * @param string $relative_path
         *            Relative path of the script. Relative to the plugin root directory.
         *            Default empty.
         * @param array $dependency
         *            Optional. An array of registered script handles this script depends on.
         *            Default empty array.
         * @param boolean $footer
         *            Optional. Whether to enqueue the script before </body> instead of in the <head>.
         *            Default 'false'.
         */
        final protected static function addScript($relative_path, $dependency = array(), $footer = false)
        {
            $uri = self::getUri($relative_path);
            $reg_data = array(
                'handle' => self::getScriptHandle($uri, 'script'),
                'uri' => $uri,
                'dependency' => $dependency,
                'footer' => $footer,
                'type' => 'script',
                'admin' => false
            );
            self::addScripts($reg_data);
        }

        /**
         * Ads front-end css file into styles que.
         * Makes very easy to add styles into que at the early stages of WordPress execution.
         *
         * @param string $relative_path
         *            Relative path of the style. Relative to the plugin root directory.
         *            Default empty.
         * @param array $dependency
         *            Optional. An array of registered stylesheet handles this stylesheet depends on.
         *            Default empty array.
         * @param string $media
         *            Optional. The media for which this stylesheet has been defined.
         *            Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
         *            '(orientation: portrait)' and '(max-width: 640px)'.
         */
        final protected static function addStyle($relative_path, $dependency = array(), $media = 'all')
        {
            $uri = self::getUri($relative_path);
            $reg_data = array(
                'handle' => self::getScriptHandle($uri),
                'uri' => $uri,
                'dependency' => $dependency,
                'media' => $media,
                'type' => 'style',
                'admin' => false
            );
            self::addScripts($reg_data);
        }

        /**
         * Loads plugin translation file.
         * Method is called automatically during WordPress execution if plugin is initialized via ::Plug() command
         * and TextDomain metadata defined in plugin metadata.
         *
         * @return boolean
         */
        final public static function loadTextDomain()
        {
            $plugin_data = self::getMetadata();
            if (isset($plugin_data->TextDomain)) {
                return load_plugin_textdomain($plugin_data->TextDomain, false, $plugin_data->language_dir);
            }
            return false;
        }

        /**
         * Enqueues WordPress back-end scripts and styles.
         * Method is called automatically during WordPress execution if plugin is initialized via ::Plug() command.
         */
        final public static function enqueueAdminStylesAndScripts()
        {
            if (is_admin()) {
                $collection = self::addScripts();
                if (is_array($collection) && isset($collection['admin'])) {
                    if (isset($collection['admin']['style']) && count($collection['admin']['style']) > 0) {
                        foreach ($collection['admin']['style'] as $key => $style) {
                            wp_enqueue_style($key, $style['uri'], $style['dependency'], null, $style['media']);
                        }
                    }
                    if (isset($collection['admin']['script']) && count($collection['admin']['script']) > 0) {
                        foreach ($collection['admin']['script'] as $key => $script) {
                            wp_enqueue_script($key, $script['uri'], $script['dependency'], null, $script['footer']);
                        }
                    }
                }
            }
        }

        /**
         * Enqueues WordPress front-end scripts and styles.
         * Method is called automatically during WordPress execution if plugin is initialized via ::Plug() command.
         */
        final public static function enqueueStylesAndScripts()
        {
            if (! is_admin()) {
                $collection = self::addScripts();
                if (is_array($collection) && isset($collection['wp'])) {
                    if (isset($collection['wp']['style']) && count($collection['wp']['style']) > 0) {
                        foreach ($collection['wp']['style'] as $key => $style) {
                            wp_enqueue_style($key, $style['uri'], $style['dependency'], null, $style['media']);
                        }
                    }
                    if (isset($collection['wp']['script']) && count($collection['wp']['script']) > 0) {
                        foreach ($collection['wp']['script'] as $key => $script) {
                            wp_enqueue_script($key, $script['uri'], $script['dependency'], null, $script['footer']);
                        }
                    }
                }
            }
        }

        /**
         * Predefined procedure to initialize an instance of WordPress plugin based on wpPlugAndPlay.
         * You can ignore this method and create your own method if needed.
         * Simplifies plugin creation and initialization by creating all basic action hooks for you.
         *
         * @see https://codex.wordpress.org/Plugin_API/Action_Reference Plugin API/Action Reference
         *     
         * @param string $hook
         *            Optional. You can specify additional method to execute after the WordPress theme is initialized.
         *            This hook is called during page load, after the WordPress theme is initialized.
         *            It is generally used to perform basic setup, registration, and init actions after the theme becomes available.
         *            Please see Wordpress documentation for more details.
         * @param string $min_php_version
         *            Optional.Minimum required PHP version. If running version of PHP is lower, plugin is not hooked and an admin notice is shown instead.
         *            Default value is "5.4".
         * @return boolean
         */
        final public static function Plug($hook = null, $min_php_version = '5.4')
        {
            static $try_plug;
            if (empty($try_plug)) {
                $i = self::getInstance();
                // Plug plugin only once
                $try_plug = true;
                // Detect PHP version
                if ($i::isPhpVersionValid($min_php_version) == false) {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                    $plg_data = self::getMetadata();
                    $name = isset($plg_data->Name) ? 'Plugin ' . $plg_data->Name : 'Plugin loader ' . $plg_data->plugin_class;
                    error_log(sprintf('PHP version: %s requires PHP version %s. Actual PHP version is %s. in %s on line %s', $name, $min_php_version, PHP_VERSION, $backtrace[0]['file'], $backtrace[0]['line']));
                    self::HookMe('showNoticePhpVersion', 'admin_notices');
                    return false;
                }
                // Register autoloader for plugin frameworks
                self::registerAutoLoad();
                // Add additional hook if defined
                if (! empty($hook)) {
                    add_action('after_setup_theme', $hook);
                }
                // Enqueue scripts and styles
                self::HookMe('enqueueStylesAndScripts', 'wp_enqueue_scripts', PHP_INT_MAX);
                self::HookMe('enqueueAdminStylesAndScripts', 'admin_enqueue_scripts', PHP_INT_MAX);
                self::HookMe('loadTextDomain', 'plugins_loaded');
                return true;
            } else {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                error_log(sprintf('PHP Coding: Method "%s" is called several times. Please check your code. in %s on line %s', self::getClassName() . '->' . __FUNCTION__, $backtrace[0]['file'], $backtrace[0]['line']));
                return false;
            }
        }

        /**
         * Hooks specified method on to a specific WordPress or used defined action.
         * Simplifies to hook any public method of plugin class to specific action.
         * Used method should be public and static.
         * Example: self::HookMe('loadTextDomain', 'plugins_loaded');
         * Example: MyPlugin::HookMe('loadTextDomain', 'plugins_loaded');
         *
         * If you use action name also for related method, then you can only specify the method name.
         * Example: self::HookMe('plugins_loaded');
         *
         * @param string $method
         *            The name of the public static method you wish to be called.
         * @param string $wp_action_tag
         *            The name of the action to which the $method is hooked.
         * @param int $priority
         *            Optional. Used to specify the order in which the functions
         *            associated with a particular action are executed. Default 10.
         *            Lower numbers correspond with earlier execution,
         *            and functions with the same priority are executed
         *            in the order in which they were added to the action.
         * @param int $accepted_args
         *            Optional. The number of arguments the function accepts. Default 1.
         */
        final public static function HookMe($method, $wp_action_tag = null, $priority = 10, $accepted_args = 1)
        {
            $wp_action_tag = is_null($wp_action_tag) ? $method : $wp_action_tag;
            add_action($wp_action_tag, self::getStaticCall($method), $priority, $accepted_args);
        }
    }
}

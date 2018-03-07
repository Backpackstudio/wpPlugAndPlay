# wpPlugAndPlay
Simplifies creation of WordPress plugins.  An abstract class of WordPress Plug and Play plugin which simplifies creation of **singleton WordPress plugin instances**. Becomes vey handy if you have to create multiple plugins, as decreses amount of code neede to write and also improves dramatically plugins performance by using the same base class for all inherited plugins.

An example how easy is to create a new plugin by using **wpPlugAndPlay**:

```php
    require_once dirname(__FILE__) . '/frameworks/wpPlugAndPlay.php';
    
    final class mySimplePlugin extends wpPlugAndPlay
    {
        public static function minPhpVersion(){return '5.6';}
    		
        protected function init()
        {
            static $initialized;
            if (empty($initialized)) {
                $initialized = true;
                //Attach my scripts and styles to WordPress
                self::myScriptsAndStyles();
            }
        }
    
        protected static function myScriptsAndStyles()
        {
            //Attach these scripts and styles to WordPress
            self::addAdminStyle('assets/css/admin-options-page.css');
            self::addStyle('assets/css/front-end-improvements.css');
            self::addAdminScript('assets/js/posts-editor-addons.js');
            self::addScript('assets/js/image-tags.js');
        }
    }
	//Initialise plugin
    mySimplePlugin::Plug();

```

Thats it! You are ready to fly!

## Plugin initialisation

Additionally you can immediately hook extra functionality by specifying related method. This is also the preferred way to attach your functionality to your plugin.

```php
    mySimplePlugin::Plug('\myNamespace\myExtension::Hook');
```
On this case you should define class "myExtension" in file ./frameworks/myNamespace/myExtension.php and its loaded automatically by PHP, no need to write include statements.

### Performance
A slow website means users will potentially leave this website before it even loads. Badly coded WordPress plugins can decrease load time of website dramatically. Proper coding can keep to run WordPress site fast and smooth. By using wpPlugAndPlay properly you can write plugins, which doesn't decrease WordPress speed dramatically. wpPlugAndPlay supports these WP code optimisation recommendations:

* Keep your plugin class very small and simple, divide all your functionality into separate classes and load it if only really necessary.
* Chunk your code into **multiple classes**, each class into separate file.
* Use **namespaces**
* Use **WP actions and filters** to hook your functionality
* Use **static methods** only for WordPress actions and filters. PERIOD.
* Use singleton classes for objects which you need the entire request lifecycle in a WordPress application.

Note: wpPlugAndPlay uses singleton pattern, so your plugin is always singleton object trough entire lifecycle of WordPress request.

## Autoloading PHP classes
wpPlugAndPlay comes with built in feature allowing PHP to load automatically the classes or interfaces which are placed in ./framworks folder in your plugin directory. It supports namespaces. For example class \MyNamespace\MyClass should be defined in file ./frameworks/MyNamespace/MyClass.php and its loaded automatically. No need to write any code to include your PHP scripts.

Note: Autoloading is not available if using PHP in CLI interactive mode.

## PHP version check

On some cases your plugin require minimum PHP version to run properly. To avoid fatal collapse of Wordpress, you can specify minimum version of PHP and wpPlugAndPlay will not load your plugin if system requirements are not met. For this purpose you have to define method minPhpVersion. 

```php
    public static function minPhpVersion(){return '5.6';}
```

If you don't need PHP version validation, then you can return FALSE from  minPhpVersion. To avoid fatal collapse of WordPress because of PHP version issues, it's strongly recommended to set minimum PHP version required for your plugin.

```php
    public static function minPhpVersion(){return false;}
```


On this case your plugin is not loaded if PHP version is lower than 5.6 and an admin notice about PHP version issue is shown instead.

Please stay tuned for documentation update. ;) 

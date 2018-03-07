# wpPlugAndPlay
Simplifies creation of WordPress plugins.  An abstract class of WordPress Plug and Play plugin which simplifies creation of **singleton WordPress plugin instances**. Becomes vey handy if you have to create multiple plugins, as decreases amount of code needed to write and also improves dramatically plugins performance by using the same base class for all inherited plugins.

An example to show how easy is to create a new plugin by using **wpPlugAndPlay**:

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

To initialise plugin you have to run very simple code. You just have to call predefined method "Plug", for example:

```php
    mySimplePlugin::Plug();
```

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

Note: wpPlugAndPlay uses singleton pattern, so your plugin is always singleton object trough entire lifecycle of WordPress request. wpPlugAndPay also offers an abstract class to use singleton pattern, so you can easily write singleton classes for your plugin.

## Autoloading PHP classes
wpPlugAndPlay comes with built in feature allowing PHP to load automatically the classes or interfaces which are placed in ./frameworks folder in your plugin directory. It supports namespaces. For example class \MyNamespace\MyClass should be defined in file ./frameworks/MyNamespace/MyClass.php and its loaded automatically. No need to write any code to include your PHP scripts.

Note: Autoloading is not available if using PHP in CLI interactive mode.

## PHP version check

On some cases your plugin require minimum PHP version to run properly. To avoid fatal collapse of Wordpress, you can specify minimum version of PHP and wpPlugAndPlay will not load your plugin if system requirements are not met. For this purpose you have to define method minPhpVersion. 

```php
    public static function minPhpVersion(){return '5.6';}
```
On this case your plugin is not loaded if PHP version is lower than 5.6 and an admin notice about PHP version issue is shown instead.

If you don't need PHP version validation, then you can return FALSE from  minPhpVersion.

```php
    public static function minPhpVersion(){return false;}
```

To avoid fatal collapse of WordPress because of PHP version issues, it's strongly recommended to set minimum PHP version required for your plugin.

## Options page

wpPlugAndPlay comes with built in WordPress Admin Options page especially for your plugin only. It generates basic options page, so you only have to add desired sections and fields into your plugin options/settings page. There are 2 predefined methods to easily add sections and fields:

* **addOptionsSection** - to add section into your plugin options
* **addOptionsField** - to add fields into your options sections

Below is an example how to add section.

```php
    self::addOptionsSection ('mysection_id', 'Section Title');
```
Below is an example how to add options field into section.

```php
    self:: addOptionsField ('mysection_id', 'my_plugin_serial', 'Field Title', '\myNamespace\myPlgOptions::showFieldSerial');
```
On this case you have do define method showFieldSerial to handle field rendering. 

### Debugging info

At the bottom of your plugin Options page is automatically added section containing some debugging information, which comes useful and handy for both, for site administrators and plugin developers. Debugging output is hidden by default, just tap on title "Debug" at the bottom of plugin options page to reveal it, tap again to hide it.

..

Please stay tuned for documentation update. ;) 

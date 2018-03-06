# wpPlugAndPlay
Simplifies creation of WordPress plugins.  An abstract class of WordPress Plug and Play plugin which simplifies creation of **singleton WordPress plugin instances**. Becomes vey handy if you have to create multiple plugins, as decreses amount of code neede to write and but also improves dramatically plugins performance by using the same base class for all inherited plugins.

An example how easy is to create a new plugin by using **wpPlugAndPlay**:

```php
    require_once dirname(__FILE__) . '/frameworks/wpPlugAndPlay.php';
    
    final class mySimplePlugin extends wpPlugAndPlay
    {
    
        /**
         * {@inheritdoc}
         * @see wpPlugAndPlay::init()
         */
        protected function init()
        {
            static $initialized;
            if (empty($initialized)) {
                $initialized = true;
                self::myScriptsAndStyles();
            }
        }
    
        protected static function myScriptsAndStyles()
        {
            self::addAdminStyle('assets/css/admin-options-page.css');
            self::addStyle('assets/css/front-end-improvements.css');
            self::addAdminScript('assets/js/posts-editor-addons.js');
            self::addScript('assets/js/image-tags.js');
        }
    }

	mySimplePlugin::Plug();

```

Thats it! You are ready to fly!

Additionally you can hook immediately additional functionality by specifying related method.

```php
	mySimplePlugin::Plug('\myNamespace\myExtension::Hook');
```
	
On some cases your plugin might require minimum PHP version to run properly. To avoid fatal collapse of Wordpress, you can specify minimum version of PHP and wpPlugAndPlay will not load your plugin if system requirements are not met.

```php
	mySimplePlugin::Plug('\myNamespace\myExtension::Hook', '5.6');
```
	
On this case your plugin is not loaded if PHP version is lower than 5.6 and an admin notice about PHP version issue is shown instead.

Please stay tuned for documentation update. ;) 

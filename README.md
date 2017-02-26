# Controller

WordPress plugin to enable a basic controller when using Blade with [Sage 9](https://roots.io/sage/).

## Installation

#### Composer:

Recommended method; [Roots Bedrock](https://roots.io/bedrock/) and [WP-CLI](http://wp-cli.org/)
```shell
$ composer require soberwp/controller
$ wp plugin activate controller
```

#### Manual:

* Download the [zip file](https://github.com/soberwp/models/archive/master.zip)
* Unzip to your sites plugin folder
* Activate via WordPress

#### Requirements:

* [PHP](http://php.net/manual/en/install.php) >= 5.6.x

## Setup

By default, create folder `controllers/` within your theme directory. 

Alternatively, you can define a custom path using the filter below within your themes `functions.php` file; 
```php

add_filter('sober/controller/path', function () {
    return get_stylesheet_directory() . '/your-custom-folder';
});
```

The controller will autoload PHP files within the above path and its subdirectories.

## Usage

#### Creating a basic Controller:

* Name the Controller file the same name as the template file.
* Extend the Controller Class&mdash;the class name does not have to match the template name but it is recommended.
* Create methods within the Controller Class;
    * Use `public static function` to expose the returned values to the blade template/s. 
    * Use `protected static function` for internal controller methods as only public methods are exposed to the template.
* Return a value from the public methods which will be passed onto the blade template.
    * **Important:** The method name is converted to snake case and becomes the variable name in the blade template.
    * **Important:** If the same method name is declared twice, the latest instance will override the previous.

#### Examples: 

The following example will expose `$images` to `templates/single.blade.php`

**[controllers/single.php](.github/controllers/single.php)**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    /**
     * Protected methods will not be passed to the template
     */
    protected function hidden()
    {
        
    }

    /**
     * Return images from Advanced Custom Fields
     *
     * @return array
     */
    public function images()
    {
        return get_field('images');
    }
}
```

**[templates/single.blade.php](.github/templates/single.blade.php)**

```php
@if($images)
  <ul>
    @foreach($images as $image)
      <li><img src="{{$image['sizes']['thumbnail']}}" alt="{{$image['alt']}}"></li>
    @endforeach
  </ul>
@endif
```

#### Creating Components;

By default, the controller matches the template filename&mdash;but you can also create reusable components and include them each view.

**[controllers/partials/images.php](.github/controllers/partials/images.php)**

```php
<?php

namespace App;

trait Images
{
    public function images()
    {
        return get_field('images');
    }
}
```

You can now include the Images trait into any view to pass on variable $images; 

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    use Images;
}
```

#### Inheriting the Tree/Heirarchy;

By default, Controller overrides it's template heirarchy.

You can inherit the WordPress heirarchy Controllers by implementing the Tree. 

The following `controllers/single.php` example will inherit methods from `controllers/singular.php`;

```php
<?php

namespace App;

use Sober\Controller\Controller;
use Sober\Controller\Tree;

class Single extends Controller implements Tree
{
    public function books()
    {
        return array(1,2,3,4,5,6);
    }
}
```

You can override a `controllers/singular.php` method by declaring the same method name in `controllers/single.php`;

#### Creating Global Properties;

Methods created in `controllers/base.php` will be inherited by all views and can not be disabled as all templates extend `templates/layouts/base.php`. 

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Base extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }
}
```

#### Disable Option;

```php
class Images extends Controller
{
    public $active = false;
}
```

#### Blade Debugging;

In your Blade templates, you can use the following to assist with debugging;

* `@debug('controller')` echos a list of variables available in the template.
* `@debug('dump')` var_dumps a list of variables available in the template, including `$post`.

## Updates

#### Composer:

* Change the composer.json version to ^1.0.0**
* Check [CHANGELOG.md](CHANGELOG.md) for any breaking changes before updating.

```shell
$ composer update
```

#### WordPress:

Includes support for [github-updater](https://github.com/afragen/github-updater) to keep track on updates through the WordPress backend.
* Download [github-updater](https://github.com/afragen/github-updater)
* Clone [github-updater](https://github.com/afragen/github-updater) to your sites plugins/ folder
* Activate via WordPress

## Social

* Twitter [@withjacoby](https://twitter.com/withjacoby)

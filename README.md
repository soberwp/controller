# Controller

WordPress plugin to enable a basic controller when using Blade with [Sage 9](https://roots.io/sage/).

## Installation

#### Composer:

Recommended method; [Roots Bedrock](https://roots.io/bedrock/) and [WP-CLI](http://wp-cli.org/)
```shell
$ composer require "soberwp/controller:1.0.0-beta1.2"
$ wp plugin activate controller
```

#### Manual:

* Download the [zip file](https://github.com/soberwp/controller/archive/master.zip)
* Unzip to your sites plugin folder
* Activate via WordPress

#### Requirements:

* [PHP](http://php.net/manual/en/install.php) >= 5.6.x

## Setup

By default, create folder `src/controllers/` within your theme directory. 

Alternatively, you can define a custom path using the filter below within your themes `functions.php` file; 
```php

add_filter('sober/controller/path', function () {
    return get_stylesheet_directory() . '/your-custom-folder';
});
```

The controller will autoload PHP files within the above path and its subdirectories.

## Usage

#### Creating a basic Controller:

* Controller files follow the same hierarchy as WordPress.
    * You can view the controller hierarchy by using the Blade directive `@debug('hierarchy')` on any template or inspecting body classes ending with *-data.
* Extend the Controller Class&mdash;the class name does not have to match the template name but it is recommended.
* Create methods within the Controller Class;
    * Use `public static function` to expose the returned values to the Blade template/s. 
    * Use `protected static function` for internal controller methods as only public methods are exposed to the template.
* Return a value from the public methods which will be passed onto the Blade template.
    * **Important:** The method name is converted to snake case and becomes the variable name in the Blade template.
    * **Important:** If the same method name is declared twice, the latest instance will override the previous.

#### Examples: 

The following example will expose `$images` to `templates/single.blade.php`

**src/controllers/single.php**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
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

**templates/single.blade.php**

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

You can also create reusable components and include them in a template using PHP traits.

**src/controllers/partials/images.php**

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

You can now include the Images trait into any template to pass on variable $images; 

**src/controllers/single.php**

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

By default, each Controller overrides its template heirarchy depending on the specificity of the Controller (the same way WordPress templates work).

You can inherit the data from less specific Controllers in the heirarchy by implementing the Tree. 

For example, the following `src/controllers/single.php` example will inherit methods from `src/controllers/singular.php`;

**src/controllers/single.php**

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

You can override a `src/controllers/singular.php` method by declaring the same method name in `src/controllers/single.php`;

#### Creating Global Properties;

Methods created in `src/controllers/base.php` will be inherited by all templates and can not be disabled as `templates/layouts/base.php` extends all templates. 

**src/controllers/base.php**

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
public $active = false;
```

#### Blade Debugging;

In your Blade templates, you can use the following to assist with debugging;

* `@debug('hierarchy')` echos a list of the controller hierarchy for the current template.
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

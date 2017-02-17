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

By default, create folder `controllers/` within your active theme directory. 

Alternatively, you can define a custom path using the filter below within your themes `functions.php` file; 
```php

add_filter('sober/controller/path', function () {
    return get_stylesheet_directory() . '/your-custom-folder';
});
```

The controller will autoload PHP files within the above path and its subdirectories.

## Usage

#### Creating a Controller:

Name the Controller file the same name as the template file or [override the `$template` variable.](#option-template)
* eg; `controllers/single.php`

Extend the Controller Class.  The class doesn't have to match the template name.
* eg: `class Single extends Controller {}`

Create methods within the Controller Class;
* Use `public static function` to expose the returned values to the blade template/s. 
* Use `protected static function` for internal controller methods (protected or private methods will not be exposed to the template).

Return a variable from the exposed public methods&mdash;which will be passed onto the blade template.

#### Example: 

The following example will expose `$images` to `templates/single.blade.php`

**[controllers/single.php](.github/controllers/single.php)**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    /**
     * Protected and Private methods will not be passed to the template
     */
    protected function hidden()
    {
        
    }

    /**
     * Return images from Advanced Custom Fields
     */
    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
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

<a name="option-template"></a>

#### Template Option:

By default, the controller matches the template filename&mdash;but you can override the template to target by using; 

* To expose to single template; 
    * `public $template = 'single;`

* To expose to multiple templates; 
    * `public $template = ['single', 'page'];`

* To expose to all templates; 
    * `public $template = 'all';`

This allows you to create controllers based around components rather than per template.

The following example will expose `$images` to both `templates/single.blade.php` and `templates/page.blade.php`

**[controllers/images.php](.github/controllers/images.php)**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Images extends Controller
{
    public $template = ['single', 'page'];

    /**
     * Return images from Advanced Custom Fields
     */
    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}
```

#### Disable Option;

```php
class Images extends Controller
{
    protected $active = false;
}
```

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

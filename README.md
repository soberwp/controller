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

By default, create folder `controllers/` within the active theme directory. 

Alternatively, you can define a custom path using the filter below within your themes `functions.php` file; 
```php

add_filter('sober/controller/path', function () {
    return get_stylesheet_directory() . '/your-custom-folder';
});
```

The controller will autoload PHP files within the above path and its subdirectories.

## Usage

#### Creating a Controller:

* Name the controller file the same name as the template file. 
  * eg; `controllers/single.php`
* Extend the Controller Class.  The class doesn't have to correlate with the template name, but it is recommended.
  * eg: `class Single extends Controller {}`
* Return a value in the function, which will be passed to the blade template.
  * The method name will become the variable name available in the blade template.
    * Use `public static function` to expose the return values to the blade template. 
    * Use `protected static function` for internal controller methods (protected methods will not be exposed to the template).

#### Example: 

The following example will expose `$images` to `templates/single.blade.php`

**[controllers/single.php](.github/controllers/single.php)**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    protected function hidden()
    {
        // protected methods will not be exposed to the blade template.
    }

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

#### Option: change the Blade template/views;

By default, the controller matches the template filename&mdash;but you can override the template to target by using; 

* To expose to single template; 
    * `public $template = 'single;`

* To expose to multiple templates; 
    * `public $template = ['single', 'page'];`

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

    public function images()
    {
        $images = get_field('images');
        if ($images) {
            return $images;
        }
    }
}
```

#### Option: disable Controller;

```php
class Images extends Controller
{
    protected $active = false;
}
```

## Updates

#### Composer:

* Change the composer.json version to ^1.0.3**
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

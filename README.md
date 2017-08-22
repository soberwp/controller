# Controller

WordPress package to enable a controller when using Blade with [Sage 9](https://roots.io/sage/).

## Installation

#### Composer:

**Please note that Controller is no longer an mu-plugin and is now a Composer theme depedency.**

Browse into the Sage theme directory and run;

```shell
$ composer require soberwp/controller:9.0.0-beta.3
```

#### Requirements:

* [PHP](http://php.net/manual/en/install.php) >= 7.0

## Setup

By default, create folder `app/controllers/` within your theme directory.

Alternatively, you can define a custom path using the filter below within your themes `functions.php` file;
```php

add_filter('sober/controller/path', function () {
    return dirname(get_template_directory()) . '/app/custom-folder';
});
```

The controller will autoload PHP files within the above path and its subdirectories.

## Usage

#### Creating a basic Controller:

* Controller files follow the same hierarchy as WordPress.
    * You can view the controller hierarchy by using the Blade directive `@debug('hierarchy')`.
* Extend the Controller Class&mdash; it is recommended that the class name matches the filename.
* Create methods within the Controller Class;
    * Use `public function` to expose the returned values to the Blade views/s.
    * Use `public static function` to use the function within your Blade view/s.
    * Use `protected function` for internal controller methods as only public methods are exposed to the view. You can run them within `__construct`.
* Return a value from the public methods which will be passed onto the Blade view.
    * **Important:** The method name is converted to snake case and becomes the variable name in the Blade view.
    * **Important:** If the same method name is declared twice, the latest instance will override the previous.

#### Examples:

The following example will expose `$images` to `resources/views/single.blade.php`

**app/controllers/Single.php**

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

**resources/views/single.blade.php**

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

You can also create reusable components and include them in a view using PHP traits.

**app/controllers/partials/Images.php**

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

**app/controllers/Single.php**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    use Images;
}
```

#### Using Static Methods;

You can use static methods to return content from your controller.

This is useful if you are within the loop and want to return data for each post item individually.

**app/controllers/Archive.php**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Archive extends Controller
{
    public static function title()
    {
        return get_post()->post_title;
    }
}
```

**resources/views/archive.php**

```php
@extends('layouts.app')

@section('content')

  @while (have_posts()) @php(the_post())
    {{ Archive::title() }}
  @endwhile

@endsection
```

#### Inheriting the Tree/Heirarchy;

By default, each Controller overrides its template heirarchy depending on the specificity of the Controller (the same way WordPress templates work).

You can inherit the data from less specific Controllers in the heirarchy by implementing the Tree.

For example, the following `app/controllers/Single.php` example will inherit methods from `app/controllers/Singular.php`;

**app/controllers/Single.php**

```php
<?php

namespace App;

use Sober\Controller\Controller;
use Sober\Controller\Module\Tree;

class Single extends Controller implements Tree
{

}
```

If you prefer you can also do this;

```php
<?php

namespace App;

use Sober\Controller\Controller;

class Single extends Controller
{
    protected $tree = true;
}
```

You can override a `app/controllers/Singular.php` method by declaring the same method name in `app/controllers/Single.php`;

#### Creating Global Properties;

Methods created in `app/controllers/App.php` will be inherited by all views and can not be disabled as `resources/views/layouts/app.php` extends all views.

**app/controllers/App.php**

```php
<?php

namespace App;

use Sober\Controller\Controller;

class App extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }
}
```

#### Disable Option;

```php
protected $active = false;
```

#### Blade Debugging;

In your Blade views, `resources/views`, you can use the following to assist with debugging;

* `@debug('hierarchy')` echos a list of the controller hierarchy for the current view.
* `@debug('controller')` echos a list of variables available in the view.
* `@debug('dump')` var_dumps a list of variables available in the view, including `$post`.

## Updates

#### Composer:

* Change the composer.json version to ^9.0.0-beta3
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

* For Controller updates and other WordPress dev, follow [@withjacoby](https://twitter.com/withjacoby)

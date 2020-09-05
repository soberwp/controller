# Controller

WordPress package to enable a controller when using Blade with [Sage 9](https://roots.io/sage/) (Please note, Sage 10 uses Composers and not this package.)

* [Installation](#installation)
* [Setup](#setup)
* [Usage](#usage)
    * [Overview](#overview)
    * [Basic Controller](#basic-controller)
    * [Using Functions](#using-functions)
    * [Using Components](#using-components)
    * [Inheriting the Tree/Hierarchy](#inheriting-the-treehierarchy)
    * [Creating Global Properties](#creating-global-properties)
    * [Advanced Custom Fields Module](#advanced-custom-fields-module)
    * [Template Override Option](#template-override-option)
    * [Lifecycles](#lifecycles)
    * [Disable Option](#disable-option)
* [Blade Debugger](#blade-debugger)
* [Blade Coder](#blade-coder)

<br>

## Installation

### Composer:

[Sage](https://roots.io/sage/) ships with Controller. However, should you need to install, browse into the Sage theme directory and run;

```shell
$ composer require soberwp/controller:2.1.2
```

### Upgrading to 2.x.x:

Please note that versions 2.x.x are newer releases than 9.x.x-beta. The 9 was used to match Sage 9 versioning at the time.

Controller 2.x.x uses [PSR4 autoloading](https://www.php-fig.org/psr/psr-4/) to load Controller classes. This is considered best practice. You will need to [update the following files](https://github.com/roots/sage/pull/2025/files) from 9.0.0-beta versions. 

Folder `controllers/` changes to `Controllers/`, class file names changes to camelcase `App.php` and `FrontPage.php`. Controller namespaces changes to `namespace App\Controllers;`

### Requirements:

* [PHP](http://php.net/manual/en/install.php) >= 7.0

## Setup

By default Controller uses namespace `Controllers`.

Controller takes advantage of [PSR-4 autoloading](https://www.php-fig.org/psr/psr-4/). To change the namespace, use the filter below within `functions.php`

```php

add_filter('sober/controller/namespace', function () {
    return 'Data';
});
```

## Usage

### Overview:

* Controller class names follow the same hierarchy as WordPress.
* The Controller class name should match the filename
    * For example `App.php` should define class as `class App extends Controller`
* Create methods within the Controller Class;
    * Use `public function` to return data to the Blade views/s
        * The method name becomes the variable name in Blade
        * Camel case is converted to snake case. `public function ExampleForUser` in the Controller becomes `$example_for_user` in the Blade template
        * If the same method name is declared twice, the latest instance will override the previous
    * Use `public static function` to use run the method from your Blade template which returns data. This is useful for loops
        * The method name is not converted to snake case
        * You access the method using the class name, followed by the method. `public static function Example` in `App.php` can be run in Blade using `App::Example()`
        * If the same method name is declared twice, the latest instance will override the previous
    * Use `protected function` for internal methods. These will not be exposed to Blade. You can run them within `__construct`
        * Dependency injection with type hinting is available through `__construct`


The above may sound complicated on first read, so let's take a look at some examples to see how simple Controller is to use.

### Basic Controller;

The following example will expose `$images` to `resources/views/single.blade.php`

**app/Controllers/Single.php**

```php
<?php

namespace App\Controllers;

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

### Using Functions;

You can use static methods to run a function from within your view.

This is useful if you are within the loop and want to return data for each post item.

**app/Controllers/Archive.php**

```php
<?php

namespace App\Controllers;

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

  @while (have_posts()) @php the_post() @endphp
    {{ Archive::title() }}
  @endwhile

@endsection
```

### Using Components;

You can also create reusable components and include them in any Controller class using PHP traits.

**app/Controllers/Partials/Images.php**

```php
<?php

namespace App\Controllers\Partials;

trait Images
{
    public function images()
    {
        return get_field('images');
    }
}
```

You can now include the Images trait into any view to pass on variable $images;

**app/Controllers/Single.php**

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Single extends Controller
{
    use Partials\Images;
}
```

### Inheriting the Tree/Hierarchy;

By default, each Controller overrides its template hierarchy depending on the specificity of the Controller (the same way WordPress templates work).

You can inherit the data from less specific Controllers in the hierarchy by implementing the Tree.

For example, the following `app/Controllers/Single.php` example will inherit methods from `app/Controllers/Singular.php`;

**app/Controllers/Single.php**

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use Sober\Controller\Module\Tree;

class Single extends Controller implements Tree
{

}
```

If you prefer you can also do this;

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Single extends Controller
{
    protected $tree = true;
}
```

You can override a `app/Controllers/Singular.php` method by declaring the same method name in `app/Controllers/Single.php`;

### Creating Global Properties;

Methods created in `app/Controllers/App.php` will be inherited by all views and can not be disabled as `resources/views/layouts/app.php` extends all views.

**app/Controllers/App.php**

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class App extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }
}
```

### Advanced Custom Fields Module;

Controller has an useful Advanced Custom Fields helper module to automate passing on fields.

The automated fields will use the variable names from Advanced Custom Fields and pass them onto the view. Controller also passes on options values by default.

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Single extends Controller
{
    // Pass on all fields from Advanced Custom Fields to the view
    protected $acf = true;

    // Pass on only field_1 from Advanced Custom Fields to the view
    protected $acf = 'field_1';

    // Pass on multiple fields from Advanced Custom Fields to the view
    protected $acf = ['field_1', 'field_2'];
}
```

Clone fields will return the value of each the fields in a separate variable, unless the _Prefix Field Names_ option is enabled in which case the the cloned fields will be returned in an object with the field name given to the clone field.

The values are returned as objects, however you can disable this to keep them as arrays.

```php
add_filter('sober/controller/acf/array', function () {
    return true;
});
```

### Template Override Option;

You should only use overrides in edge-case scenarios. Sticking to the WordPress hierarchy is recommended usage. However, one edge-case is the 404 template.

In your Blade view, you would have `404.blade.php` as it begins with a number. In this case, you could rename your Controller class `FourZeroFour.php` and use parameter `$template = '404';`

```php
<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class FourZeroFour extends Controller
{
    protected $template = '404';
}
```

### Lifecycles;

Controller Classes come with two lifecycle hooks for greater control. 

```php
public function __before()
{
    // runs after this->data is set up, but before the class methods are run
}

public function __after()
{
    // runs after all the class methods have run
}
```

### Disable Option;

```php
protected $active = false;
```

### Blade Debugger;

In your Blade views, `resources/views`, you can use the following to assist with debugging;

* `@debug`
* `@dump(__var__)`

### Blade Coder;

In your Blade views, `resources/views`, you can use the following to assist with jump-starting coding;

* `@code`
* `@code('__name of variable as string__')`

To wrap the code in if statements, use `@codeif`

* `@codeif`
* `@codeif('__name of variable as string__')`

## Support

* Follow [@withjacoby](https://twitter.com/withjacoby) on Twitter
* Buy me a beer or pay my rent, [paypal.me/darrenjacoby](https://paypal.me/darrenjacoby)

## Updates

* Change the composer.json version to 2.1.2
* Check [CHANGELOG.md](CHANGELOG.md) for any breaking changes before updating.

```shell
$ composer update
```

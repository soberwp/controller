### 2.1.2:
* Fixes

### 2.1.1:
* Update Brain/Hierarchy to 2.4.0 for CPT templates [#86](https://github.com/soberwp/controller/issues/86)
* Add filter sober/controller/sage/namespace to allow for a custom Sage namespace [#104](https://github.com/soberwp/controller/issues/104)
* Update ACF class to support taxonomy fields by default by using get_queried_object() [#101](https://github.com/soberwp/controller/issues/101)
* Bug fix for ACF class if no fields on page/post [#102](https://github.com/soberwp/controller/issues/102)
* Allow interaction in Controller with the $post object by using $this->post vs $this->data['post']

### 2.1.0:
* Update deps
* Pass in field data from Acf Options under App class
* Change $this->data from private to protected param
* Fix $post bug not appearing in the $this->data
* Fix Controller overriding filter $data
* Add filter to return Acf data as array
* Add __before and __after lifecycles
* @code and @codeif

### 2.0.1:
* Fix bug assuming Controllers/ folder name

### 2.0.0:
* PSR4 loading
* Template overrides for those underscores
* Pass in field data from Acf automatically
* Debugger to include static methods
* Improve Debugger results
* Dependency injection
* Bug fixes
* Change default path from resources/controllers to app/controllers

### 9.0.0-beta.3:
* Changed to Composer package
* Fix for app Controller bug
* Rename base to app
* Change default path from src/controllers to resources/controllers

### 9.0.0-beta.2.1:
* Fix for base Controller bug

### 9.0.0-beta.2:
* Align with Sage9 versioning
* Enable the use of __construct within the child Class

### 1.0.2:
* Prevent public static methods from being passed onto data
* Class alias for use in template

### 1.0.1:
* Pass on default post data for posts
* Show $post in the controller debugger

### 1.0.0:
* Release

CorsSlim
========

[Cross-origin resource sharing](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) (CORS) Middleware for PHP [Slim Framework](http://www.slimframework.com/).

[![Latest Stable Version](https://poser.pugx.org/palanik/corsslim/v/stable.svg)](https://packagist.org/packages/palanik/corsslim)
[![Build Status](https://travis-ci.org/palanik/CorsSlim.svg)](https://travis-ci.org/palanik/CorsSlim)
[![License](https://poser.pugx.org/palanik/corsslim/license.svg)](https://github.com/palanik/CorsSlim/blob/master/LICENSE)

## Usage ##
### Composer Autoloader ###

#### Install with [Composer](https://packagist.org/packages/palanik/corsslim) ####
1. Update your `composer.json` to require `palanik/corsslim` package.
2. Run `composer install` to add CorsSlim your vendor folder.
```json
{
  "require": {
    "palanik/corsslim": "*"
  }
}
```
#### Autoloading ####
```php
<?php
require ('./vendor/autoload.php');

$app = new \Slim\Slim();

$app->add(new \CorsSlim\CorsSlim());
?>
```

### Custom Load ###
```php
<?php
\Slim\Slim::registerAutoLoader();

$app = new \Slim\Slim();

require ('path_to_your_middlewares/CorsSlim.php');
$app->add(new \CorsSlim\CorsSlim());
?>
```

## Options ##
You can create the middleware with custom options. Pass options as associative array.
* `origin` => The value to set for **[Access-Control-Allow-Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Origin)** response header. Default value is '\*'.
* `exposeHeaders` => The value to set for **[Access-Control-Expose-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Expose-Headers)** response header. Pass an array of strings.
* `maxAge` => The value to set for **[Access-Control-Max-Age](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Max-Age)** response header.
* `allowCredentials` => The value to set for **[Access-Control-Allow-Credentials](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Credentials)** response header. Pass True/False.
* `allowMethods` => The value to set for **[Access-Control-Allow-Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Methods)** response header. Pass an array of allowed method names. Default values are `GET,HEAD,PUT,POST,DELETE`.
* `allowHeaders` => The value to set for **[Access-Control-Allow-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Headers)** response header. Pass an array of allowed headers.

### Example ###
```php
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("X-My-Custom-Header", "X-Another-Custom-Header"),
    "maxAge" => 1728000,
    "allowCredentials" => True,
    "allowMethods" => array("POST, GET"),
    "allowHeaders" => array("X-PINGOTHER")
    );
$cors = new \CorsSlim\CorsSlim($corsOptions);
```

## Whitelisted Origins ##
Set an array of allowed origins to `origin` option. If a matching request origin found it is used.

### Example ###
```php
$corsOptions = array(
    "origin" => array('http://one.allowed-origin.com', 'http://two.allowed-origin.com),
    "exposeHeaders" => array("X-My-Custom-Header", "X-Another-Custom-Header"),
    "maxAge" => 1728000,
    "allowCredentials" => True,
    "allowMethods" => array("POST, GET"),
    "allowHeaders" => array("X-PINGOTHER")
    );
$cors = new \CorsSlim\CorsSlim($corsOptions);
```

## Route Middleware ##
##### *New* #####
You can now enable cors selectively for individual routes. 

Use the static method `routeMiddleware` to create and add cors middleware to specific routes.

```php
<?php
require ('./vendor/autoload.php');
$app = new \Slim\Slim();

$app->get('/item/:id', 
          \CorsSlim\CorsSlim::routeMiddleware(), 
          function ($name) use ($app) {
            ...
          }
        );
?>
```

Also with custom [options](#options).
```php
<?php
require ('./vendor/autoload.php');
$app = new \Slim\Slim();

$corsOptions = array("origin" => "*");
$app->get('/item/:id', 
          \CorsSlim\CorsSlim::routeMiddleware($corsOptions), 
          function ($name) use ($app) {
            ...
          }
        );

?>
```

For [Preflighted requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Preflighted_requests), provide `OPTIONS` implementation for the corresponding routes.

```php
<?php
require ('./vendor/autoload.php');
$app = new \Slim\Slim();

$app->options('/item', 
          \CorsSlim\CorsSlim::routeMiddleware(), 
          function ($name) use ($app) {}
        );
$app->post('/item', 
          \CorsSlim\CorsSlim::routeMiddleware(), 
          function ($name) use ($app) {
            ...
          }
        );

?>
```
## License ##

  [MIT](LICENSE)

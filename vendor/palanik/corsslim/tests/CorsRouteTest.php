<?php
namespace CorsSlim\Tests;

class CorsSlimRouteTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        ob_start();
    }
    public function tearDown() {
        ob_end_clean();
    }

    private function runApp($action, $actionName, $mwOptions = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        $app->setName($actionName);

        $mw = function() {
            // Do nothing
        };
        if (isset($mwOptions)) {
            if (is_callable($mwOptions)) {
                $mw = $mwOptions;
            }
            else {
                $mwOptions['appName'] = $actionName;
                $mw = \CorsSlim\CorsSlim::routeMiddleware($mwOptions);
            }
        }

        $app->get('/:name', $mw, function ($name) use ($app, $action) {
            if ($app->request->isHead()) {
                $app->status(204);
                return;
            }

            $app->contentType('application/json');
            $app->response->write(json_encode(array(
                                                "action" => $action,
                                                "method" => "GET",
                                                "name" => $name
                                                )
                                            )
                                    );
        });

        foreach ($headers as $key => $value) {
            $app->request->headers()->set($key, $value);
        }

        $app->run();

        $this->validate($app, 'GET', $action, $actionName);

        return $app;
    }

    private function runAppHead($action, $actionName, $mwOptions = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        $app->setName($actionName);

        $mw = function() {
            // Do nothing
        };
        if (isset($mwOptions)) {
            if (is_callable($mwOptions)) {
                $mw = $mwOptions;
            }
            else {
                $mwOptions['appName'] = $actionName;
                $mw = \CorsSlim\CorsSlim::routeMiddleware($mwOptions);
            }
        }

        $app->get('/:name', $mw, function ($name) use ($app, $action) {
            if ($app->request->isHead()) {
                $app->status(204);
                return;
            }

            $app->contentType('application/json');
            $app->response->write(json_encode(array(
                                                "action" => $action,
                                                "method" => "GET",
                                                "name" => $name
                                                )
                                            )
                                    );
        });

        foreach ($headers as $key => $value) {
            $app->request->headers()->set($key, $value);
        }

        $app->run();

        $this->assertEquals(204, $app->response()->status());

        return $app;
    }

    private function runAppPost($action, $actionName, $mwOptions = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        $app->setName($actionName);

        $mw = function() {
            // Do nothing
        };
        if (isset($mwOptions)) {
            if (is_callable($mwOptions)) {
                $mw = $mwOptions;
            }
            else {
                $mwOptions['appName'] = $actionName;
                $mw = \CorsSlim\CorsSlim::routeMiddleware($mwOptions);
            }
        }

        $app->post('/:name', $mw, function ($name) use ($app, $action) {
            if ($app->request->isHead()) {
                $app->status(204);
                return;
            }

            $app->contentType('application/json');
            $app->response->write(json_encode(array(
                                                "action" => $action,
                                                "method" => "POST",
                                                "name" => $name
                                                )
                                            )
                                    );
        });

        foreach ($headers as $key => $value) {
            $app->request->headers()->set($key, $value);
        }

        $app->run();

        $this->validate($app, 'POST', $action, $actionName);

        return $app;
    }

    private function runAppPreFlight($action, $actionName, $mwOptions = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'OPTIONS',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        $app->setName($actionName);

        $mw = function() {
            // Do nothing
        };
        if (isset($mwOptions)) {
            if (is_callable($mwOptions)) {
                $mw = $mwOptions;
            }
            else {
                $mwOptions['appName'] = $actionName;
                $mw = \CorsSlim\CorsSlim::routeMiddleware($mwOptions);
            }
        }

        $app->options('/:name', $mw, function ($name) use ($app, $action) {
        });

        $app->delete('/:name', $mw, function ($name) use ($app, $action) {
            if ($app->request->isHead()) {
                $app->status(204);
                return;
            }


            $app->contentType('application/json');
            $app->response->write(json_encode(array(
                                                "action" => $action,
                                                "method" => "DELETE",
                                                "name" => $name
                                                )
                                            )
                                    );
        });

        foreach ($headers as $key => $value) {
            $app->request->headers()->set($key, $value);
        }

        $app->run();

        return $app;
    }

    private function validate($app, $method, $action, $name) {
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals("application/json", $app->response()->header("Content-Type"));

        $content = json_decode($app->response()->body());
        $this->assertEquals($action, $content->action);
        $this->assertEquals($method, $content->method);
        $this->assertEquals($name, $content->name);
    }

    public function testDefaultCors() {
        $app = $this->runApp('cors', 'DefaultCors', array());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testCorsOrigin() {
        $app = $this->runApp('cors-origin', 'CorsOrigin', array("origin" => "*"));
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testCorsOriginSingle() {
        $app = $this->runApp('cors-origin-single', 'CorsOriginSingle', array("origin" => "http://github.com", "appName" => "CorsOriginSingle"));
        $this->assertEquals("http://github.com", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testCorsOriginArray() {
        $app = $this->runApp('cors-origin-array', 'CorsOriginArray', array("origin" => array("http://mozilla.com", "http://php.net", "http://github.com")));
        $this->assertEquals("http://mozilla.com", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    public function testCorsOriginArraySpecific() {
        $mwOptions = array("origin" => array("http://mozilla.com", "http://php.net", "http://github.com"));
        $headers = array('origin' => 'http://php.net');
        $app = $this->runApp('cors-origin-array-specific', 'CorsOriginArraySpecific', $mwOptions, $headers);
        $this->assertEquals("http://php.net", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    public function testCorsOriginCallable() {
        $mwOptions = array("origin" => function($reqOrigin) { return $reqOrigin;});
        $headers = array('origin' => 'http://www.slimframework.com/');
        $app = $this->runApp('cors-origin-callable', 'CorsOriginCallable', $mwOptions, $headers);
        $this->assertEquals("http://www.slimframework.com/", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    // Simple Requests
    public function testSimpleCorsRequestFail() {
        $app = $this->runApp('cors', 'SimpleCorsRequestFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequest() {
        $app = $this->runApp('cors', 'SimpleCorsRequest', array());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestHeadFail() {
        $app = $this->runAppHead('cors', 'SimpleCorsRequestHeadFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestHead() {
        $app = $this->runAppHead('cors', 'SimpleCorsRequestHead', array());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestPostFail() {
        $app = $this->runAppPost('cors', 'SimpleCorsRequestPostFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestPost() {
        $app = $this->runAppPost('cors', 'SimpleCorsRequestPost', array());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    // Complex Requests (With Pre-Flight)
    public function testComplexCorsRequestPreFlightFail() {
        $app = $this->runAppPreFlight('cors', 'ComplexCorsRequestPreFlightFail');
        $this->assertEquals(200, $app->response()->status());
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testComplexCorsRequestPreFlight() {
        $app = $this->runAppPreFlight('cors', 'ComplexCorsRequestPreFlight', array());
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    // Access-Control-Expose-Headers
    public function testAccessControlExposeHeaders() {
        $app = $this->runApp('cors', 'SimpleCorsRequestAccessControlExposeHeaders', array('exposeHeaders' => 'X-My-Custom-Header'));
        $this->assertEquals("X-My-Custom-Header", $app->response()->header("Access-Control-Expose-Headers"));
    }

    public function testAccessControlExposeHeadersArray() {
        $app = $this->runApp('cors', 'SimpleCorsRequesAccessControlExposeHeadersArrayt', array('exposeHeaders' => array("X-My-Custom-Header", "X-Another-Custom-Header")));
        $this->assertEquals("X-My-Custom-Header, X-Another-Custom-Header", $app->response()->header("Access-Control-Expose-Headers"));
    }

    // Access-Control-Max-Age
    public function testAccessControlMaxAge() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlMaxAge', array('maxAge' => 1728000));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals(1728000, $app->response()->header("Access-Control-Max-Age"));
    }

    // Access-Control-Allow-Credentials
    public function testAccessControlAllowCredentials() {
        $app = $this->runApp('cors', 'SimpleCorsRequestAccessControlAllowCredentials', array('allowCredentials' => True));
        $this->assertEquals("true", $app->response()->header("Access-Control-Allow-Credentials"));
    }

    // Access-Control-Allow-Methods
    public function testAccessControlAllowMethods() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlAllowMethods', array('allowMethods' => array('GET', 'POST')));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals('GET, POST', $app->response()->header("Access-Control-Allow-Methods"));
    }

    // Access-Control-Allow-Headers
    public function testAccessControlAllowHeaders() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlAllowHeaders', array("allowHeaders" => array("X-PINGOTHER")));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals('X-PINGOTHER', $app->response()->header("Access-Control-Allow-Headers"));
    }
}
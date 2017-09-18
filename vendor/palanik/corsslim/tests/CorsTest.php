<?php
namespace CorsSlim\Tests;

class CorsSlimTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        ob_start();
    }
    public function tearDown() {
        ob_end_clean();
    }

    private function runApp($action, $actionName, $mw = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        if (isset($mw)) {
            $app->add($mw);
        }

        $app->get('/:name', function ($name) use ($app, $action) {
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

    private function runAppHead($action, $actionName, $mw = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        if (isset($mw)) {
            $app->add($mw);
        }

        $app->get('/:name', function ($name) use ($app, $action) {
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

    private function runAppPost($action, $actionName, $mw = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        if (isset($mw)) {
            $app->add($mw);
        }

        $app->post('/:name', function ($name) use ($app, $action) {
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

    private function runAppPreFlight($action, $actionName, $mw = NULL, $headers = array()) {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'OPTIONS',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'application/json',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'. $actionName
        ));
        $app = new \Slim\Slim();
        if (isset($mw)) {
            $app->add($mw);
        }


        $app->delete('/:name', function ($name) use ($app, $action) {
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

    public function testNoCors() {
        $app = $this->runApp('nocors', "NoCors");
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }


    public function testDefaultCors() {
        $app = $this->runApp('cors', 'DefaultCors', new \CorsSlim\CorsSlim());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testCorsOrigin() {
        $app = $this->runApp('cors-origin', 'CorsOrigin', new \CorsSlim\CorsSlim(array("origin" => "*")));
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testCorsOriginSingle() {
        $app = $this->runApp('cors-origin-single', 'CorsOriginSingle', new \CorsSlim\CorsSlim(array("origin" => "http://github.com")));
        $this->assertEquals("http://github.com", $app->response()->header("Access-Control-Allow-Origin"));
    }


    public function testCorsOriginArray() {
        $app = $this->runApp('cors-origin-array', 'CorsOriginArray', new \CorsSlim\CorsSlim(array("origin" => array("http://mozilla.com", "http://php.net", "http://github.com"))));
        $this->assertEquals("http://mozilla.com", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    public function testCorsOriginArraySpecific() {
        $mw = new \CorsSlim\CorsSlim(array("origin" => array("http://mozilla.com", "http://php.net", "http://github.com")));
        $headers = array('origin' => 'http://php.net');
        $app = $this->runApp('cors-origin-array-specific', 'CorsOriginArraySpecific', $mw, $headers);
        $this->assertEquals("http://php.net", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    public function testCorsOriginCallable() {
        $mw = new \CorsSlim\CorsSlim(array("origin" => function($reqOrigin) { return $reqOrigin;}));
        $headers = array('origin' => 'http://www.slimframework.com/');
        $app = $this->runApp('cors-origin-callable', 'CorsOriginCallable', $mw, $headers);
        $this->assertEquals("http://www.slimframework.com/", $app->response()->header("Access-Control-Allow-Origin"));        
    }

    // Simple Requests
    public function testSimpleCorsRequestFail() {
        $app = $this->runApp('cors', 'SimpleCorsRequestFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequest() {
        $app = $this->runApp('cors', 'SimpleCorsRequest', new \CorsSlim\CorsSlim());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestHeadFail() {
        $app = $this->runAppHead('cors', 'SimpleCorsRequestHeadFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestHead() {
        $app = $this->runAppHead('cors', 'SimpleCorsRequestHead', new \CorsSlim\CorsSlim());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestPostFail() {
        $app = $this->runAppPost('cors', 'SimpleCorsRequestPostFail');
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testSimpleCorsRequestPost() {
        $app = $this->runAppPost('cors', 'SimpleCorsRequestPost', new \CorsSlim\CorsSlim());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    // Complex Requests (With Pre-Flight)
    public function testComplexCorsRequestPreFlightFail() {
        $app = $this->runAppPreFlight('cors', 'ComplexCorsRequestPreFlightFail');
        $this->assertEquals(404, $app->response()->status());
        $this->assertNull($app->response()->header("Access-Control-Allow-Origin"));
    }

    public function testComplexCorsRequestPreFlight() {
        $app = $this->runAppPreFlight('cors', 'ComplexCorsRequestPreFlight', new \CorsSlim\CorsSlim());
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals("*", $app->response()->header("Access-Control-Allow-Origin"));
    }

    // Access-Control-Expose-Headers
    public function testAccessControlExposeHeaders() {
        $app = $this->runApp('cors', 'SimpleCorsRequestAccessControlExposeHeaders', new \CorsSlim\CorsSlim(array('exposeHeaders' => 'X-My-Custom-Header')));
        $this->assertEquals("X-My-Custom-Header", $app->response()->header("Access-Control-Expose-Headers"));
    }

    public function testAccessControlExposeHeadersArray() {
        $app = $this->runApp('cors', 'SimpleCorsRequesAccessControlExposeHeadersArrayt', new \CorsSlim\CorsSlim(array('exposeHeaders' => array("X-My-Custom-Header", "X-Another-Custom-Header"))));
        $this->assertEquals("X-My-Custom-Header, X-Another-Custom-Header", $app->response()->header("Access-Control-Expose-Headers"));
    }

    // Access-Control-Max-Age
    public function testAccessControlMaxAge() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlMaxAge', new \CorsSlim\CorsSlim(array('maxAge' => 1728000)));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals(1728000, $app->response()->header("Access-Control-Max-Age"));
    }

    // Access-Control-Allow-Credentials
    public function testAccessControlAllowCredentials() {
        $app = $this->runApp('cors', 'SimpleCorsRequestAccessControlAllowCredentials', new \CorsSlim\CorsSlim(array('allowCredentials' => True)));
        $this->assertEquals("true", $app->response()->header("Access-Control-Allow-Credentials"));
    }

    // Access-Control-Allow-Methods
    public function testAccessControlAllowMethods() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlAllowMethods', new \CorsSlim\CorsSlim(array('allowMethods' => array('GET', 'POST'))));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals('GET, POST', $app->response()->header("Access-Control-Allow-Methods"));
    }

    // Access-Control-Allow-Headers
    public function testAccessControlAllowHeaders() {
        $app = $this->runAppPreFlight('cors', 'SimpleCorsRequestAccessControlAllowHeaders', new \CorsSlim\CorsSlim(array("allowHeaders" => array("X-PINGOTHER"))));
        $this->assertEquals(200, $app->response()->status());
        $this->assertEquals('X-PINGOTHER', $app->response()->header("Access-Control-Allow-Headers"));
    }
}
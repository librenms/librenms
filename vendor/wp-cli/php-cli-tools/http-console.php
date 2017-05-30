<?php
/**
 * An example application using php-cli-tools and Buzz
 */

require_once __DIR__ . '/vendor/autoload.php';

define('BUZZ_PATH', realpath('../Buzz'));
define('SCRIPT_NAME', array_shift($argv));

require_once BUZZ_PATH . '/lib/Buzz/ClassLoader.php';
Buzz\ClassLoader::register();

class HttpConsole {
    protected $_host;
    protected $_prompt;

    public function __construct($host) {
        $this->_host = 'http://' . $host;
        $this->_prompt = '%K' . $this->_host . '%n/%K>%n ';
    }

    public function handleRequest($type, $path) {
        $request = new Buzz\Message\Request($type, $path, $this->_host);
        $response = new Buzz\Message\Response;

        $client = new Buzz\Client\FileGetContents();
        $client->send($request, $response);

        // Display headers
        foreach ($response->getHeaders() as $i => $header) {
            if ($i == 0) {
                \cli\line('%G{:header}%n', compact('header'));
                continue;
            }

            list($key, $value) = explode(': ', $header, 2);
            \cli\line('%W{:key}%n: {:value}', compact('key', 'value'));
        }
        \cli\line("\n");
        print $response->getContent() . "\n";

        switch ($type) {
        }
    }

    public function run() {
        while (true) {
            $cmd = \cli\prompt($this->_prompt, false, null);

            if (preg_match('/^(HEAD|GET|POST|PUT|DELETE) (\S+)$/', $cmd, $matches)) {
                $this->handleRequest($matches[1], $matches[2]);
                continue;
            }

            if ($cmd == '\q') {
                break;
            }
        }
    }
}

try {
    $console = new HttpConsole(array_shift($argv) ?: '127.0.0.1:80');
    $console->run();
} catch (\Exception $e) {
    \cli\err("\n\n%R" . $e->getMessage() . "%n\n");
}

?>

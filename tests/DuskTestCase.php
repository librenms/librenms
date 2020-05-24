<?php

namespace LibreNMS\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as baseCreateApplication;
    }

    public function createApplication()
    {
        $app = $this->baseCreateApplication();

        // set database to persistent sqlite and make sure it exists
        $database = 'testing_persistent';
        touch($app->make('config')->get("database.connections.$database.database"));
        $app->make('config')->set('database.default', $database);

        return $app;
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $arguments = [
            '--disable-gpu',
        ];

        if (getenv('CHROME_HEADLESS')) {
            $arguments[] = '--headless';
        }

        return RemoteWebDriver::create('http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            (new ChromeOptions)->addArguments($arguments)
        ));
    }
}

<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Point;
use InfluxDB\ResultSet;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class AdminTest extends AbstractTest
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     *
     */
    public function testCreateUser()
    {
        $adminObject = $this->getAdminObject(true);

        $this->assertEquals(
            new ResultSet($this->emptyResult),
            $adminObject->createUser('test', 'test', Client\Admin::PRIVILEGE_ALL)
        );
    }

    public function testChangeUserPassword()
    {
        $adminObject = $this->getAdminObject(true);

        $this->assertEquals(
            new ResultSet($this->emptyResult),
            $adminObject->changeUserPassword('test', 'test')
        );
    }

    public function testShowUsers()
    {
        $testJson = file_get_contents(dirname(__FILE__) . '/json/result-test-users.example.json');

        $clientMock = $this->getClientMock();
        $testResult = new ResultSet($testJson);

        $clientMock->expects($this->once())
            ->method('query')
            ->will($this->returnValue($testResult));

        $adminMock = new Client\Admin($clientMock);

        $this->assertEquals($testResult, $adminMock->showUsers());
    }

    /**
     * @return Client\Admin
     */
    private function getAdminObject()
    {
        return new Client\Admin($this->getClientMock(true));
    }

}
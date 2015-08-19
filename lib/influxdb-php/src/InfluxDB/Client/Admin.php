<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Client;


use InfluxDB\Client;
use InfluxDB\Database;

/**
 * Class Admin
 *
 * @package InfluxDB\Client
 */
class Admin
{
    /**
     * @var Client
     */
    private $client;

    const PRIVILEGE_READ = 'READ';
    const PRIVILEGE_WRITE = 'WRITE';
    const PRIVILEGE_ALL= 'ALL';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a user
     *
     * @param string $username
     * @param string $password
     *
     * @param string $privilege
     *
     * @throws \InfluxDB\Exception
     * @return \InfluxDB\ResultSet
     */
    public function createUser($username, $password, $privilege = null)
    {
        $query = sprintf('CREATE USER %s WITH PASSWORD \'%s\'', $username, $password);

        if ($privilege) {
            $query .= " WITH $privilege PRIVILEGES";
        }

        return $this->client->query(null, $query);
    }

    /**
     * @param string $username
     *
     * @return \InfluxDB\ResultSet
     * @throws \InfluxDB\Exception
     */
    public function dropUser($username)
    {
        return $this->client->query(null, 'DROP USER ' . $username);
    }

    /**
     * Change a users password
     *
     * @param string $username
     * @param string $newPassword
     *
     * @return \InfluxDB\ResultSet
     * @throws \InfluxDB\Exception
     */
    public function changeUserPassword($username, $newPassword)
    {
        return $this->client->query(null, "SET PASSWORD FOR $username = '$newPassword'");
    }

    /**
     * Shows a list of all the users
     *
     * @return \InfluxDB\ResultSet
     * @throws \InfluxDB\Exception
     */
    public function showUsers()
    {
        return $this->client->query(null, "SHOW USERS");
    }

    /**
     * Grants permissions
     *
     * @param string          $privilege
     * @param string          $username
     * @param Database|string $database
     *
     * @return \InfluxDB\ResultSet
     */
    public function grant($privilege, $username, $database = null)
    {
        return $this->executePrivilege('GRANT', $privilege, $username, $database);
    }

    /**
     * Revokes permissions
     *
     * @param string          $privilege
     * @param string          $username
     * @param Database|string $database
     *
     * @throws \InfluxDB\Exception
     * @return \InfluxDB\ResultSet
     */
    public function revoke($privilege, $username, $database = null)
    {
        return $this->executePrivilege('REVOKE', $privilege, $username, $database);
    }

    /**
     * @param string          $type
     * @param string          $privilege
     * @param string          $username
     * @param Database|string $database
     *
     * @throws \InfluxDB\Exception
     * @return \InfluxDB\ResultSet
     */
    private function executePrivilege($type, $privilege, $username, $database = null)
    {

        if (!in_array($privilege, [self::PRIVILEGE_READ, self::PRIVILEGE_WRITE, self::PRIVILEGE_ALL])) {
            throw new Exception($privilege . ' is not a valid privileges, allowed privileges: READ, WRITE, ALL');
        }

        if ($privilege != self::PRIVILEGE_ALL && !$database) {
            throw new Exception('Only grant ALL cluster-wide privileges are allowed');
        }

        $database = ($database instanceof Database ? $database->getName() : (string) $database);

        $query = "$type $privilege";

        if ($database) {
            $query .= sprintf(' ON %s ', $database);
        } else {
            $query .= " PRIVILEGES ";
        }

        if ($username && $type == 'GRANT') {
            $query .= "TO $username";
        } elseif ($username && $type == 'REVOKE') {
            $query .= "FROM $username";
        }

        return $this->client->query(null, $query);
    }
}
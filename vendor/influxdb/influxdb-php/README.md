# influxdb-php
## InfluxDB client library for PHP
[![Build Status](https://travis-ci.org/influxdata/influxdb-php.svg?branch=master)](https://travis-ci.org/influxdata/influxdb-php)
[![Code Climate](https://codeclimate.com/github/influxdata/influxdb-php/badges/gpa.svg)](https://codeclimate.com/github/influxdata/influxdb-php)
[![Test Coverage](https://codeclimate.com/github/influxdata/influxdb-php/badges/coverage.svg)](https://codeclimate.com/github/influxdata/influxdb-php/coverage)

### Overview

A easy to use library for using InfluxDB with PHP. Maintained by [@thecodeassassin](https://github.com/thecodeassassin), [@gianarb](https://github.com/gianarb).

The influxdb-php library was created to have php port of the python influxdb client.
This way there will be a common abstraction library between different programming languages.

### Installation

Installation can be done with composer:

``` bash
$ composer require influxdb/influxdb-php
```

### NOTE for PHP 5.3 and PHP 5.4 users

If you use either PHP 5.3 and PHP 5.4, the 0.1.x release is still supported (bug fixes and new release fixes).
The 0.1.x branch will work on PHP 5.3 and PHP 5.4 but doesn't contain all the features that the 1.0.0 release has such as UDP support.

### Getting started

Initialize a new client object:

```php
$client = new InfluxDB\Client($host, $port);
```

This will create a new client object which you can use to read and write points to InfluxDB.

It's also possible to create a client from a DSN (Data Source Name):

```php
// directly get the database object
$database = InfluxDB\Client::fromDSN(sprintf('influxdb://user:pass@%s:%s/%s', $host, $port, $dbname));

// get the client to retrieve other databases
$client = $database->getClient();
```

### Reading

To fetch records from InfluxDB you can do a query directly on a database:

```php
// fetch the database
$database = $client->selectDB('influx_test_db');

// executing a query will yield a resultset object
$result = $database->query('select * from test_metric LIMIT 5');

// get the points from the resultset yields an array
$points = $result->getPoints();
```

It's also possible to use the QueryBuilder object. This is a class that simplifies the process of building queries.

```php
// retrieve points with the query builder
$result = $database->getQueryBuilder()
	->select('cpucount')
	->from('test_metric')
	->limit(2)
	->getResultSet()
	->getPoints();

// get the query from the QueryBuilder
$query = $database->getQueryBuilder()
	->select('cpucount')
	->from('test_metric')
	->where(["region = 'us-west'"])
	->getQuery();
```

Make sure that you enter single quotes when doing a where query on strings; otherwise InfluxDB will return an empty result.

You can get the last executed query from the client:

```php
// use the getLastQuery() method
$lastQuery = $client->getLastQuery();

// or access the static variable directly:
$lastQuery = Client::lastQuery;
```

### Writing data

Writing data is done by providing an array of points to the writePoints method on a database:

```php
// create an array of points
$points = array(
	new Point(
		'test_metric', // name of the measurement
		0.64, // the measurement value
		['host' => 'server01', 'region' => 'us-west'], // optional tags
		['cpucount' => 10], // optional additional fields
		1435255849 // Time precision has to be set to seconds!
	),
    new Point(
    	'test_metric', // name of the measurement
		0.84, // the measurement value
		['host' => 'server01', 'region' => 'us-west'], // optional tags
		['cpucount' => 10], // optional additional fields
		1435255849 // Time precision has to be set to seconds!
	)
);

// we are writing unix timestamps, which have a second precision
$result = $database->writePoints($points, Database::PRECISION_SECONDS);
```

It's possible to add multiple [fields](https://docs.influxdata.com/influxdb/latest/concepts/key_concepts/) when writing
measurements to InfluxDB. The point class allows one to easily write data in batches to influxDB.

The name of a measurement and the value are mandatory. Additional fields, tags and a timestamp are optional.
InfluxDB takes the current time as the default timestamp.

You can also write multiple fields to a measurement without specifying a value:

```php
$points = [
	new Point(
		'instance', // the name of the measurement
		null, // measurement value
		['host' => 'server01', 'region' => 'us-west'], // measurement tags
		['cpucount' => 10, 'free' => 1], // measurement fields
		exec('date +%s%N') // timestamp in nanoseconds on Linux ONLY
	),
	new Point(
		'instance', // the name of the measurement
		null, // measurement value
		['host' => 'server01', 'region' => 'us-west'], // measurement tags
		['cpucount' => 10, 'free' => 2], // measurement fields
		exec('date +%s%N') // timestamp in nanoseconds on Linux ONLY
	)
];

```

#### Writing data using udp

First, set your InfluxDB host to support incoming UDP sockets:

```ini
[udp]
  enabled = true
  bind-address = ":4444"
  database = "test_db"
```

Then, configure the UDP driver in the client:

```php
// set the UDP driver in the client
$client->setDriver(new \InfluxDB\Driver\UDP($client->getHost(), 4444));

$points = [
	new Point(
		'test_metric',
		0.84,
		['host' => 'server01', 'region' => 'us-west'],
		['cpucount' => 10],
		exec('date +%s%N') // this will produce a nanosecond timestamp on Linux ONLY
	)
];

// now just write your points like you normally would
$result = $database->writePoints($points);
```

Or simply use a DSN (Data Source Name) to send metrics using UDP:

```php
// get a database object using a DSN (Data Source Name)
$database = \InfluxDB\Client::fromDSN('udp+influxdb://username:pass@localhost:4444/test123');

// write your points
$result = $database->writePoints($points);
```

*Note:* It is import to note that precision will be *ignored* when you use UDP. You should always use nanosecond
precision when writing data to InfluxDB using UDP.

#### Timestamp precision

It's important to provide the correct precision when adding a timestamp to a Point object. This is because
if you specify a timestamp in seconds and the default (nanosecond) precision is set; the entered timestamp will be invalid.

```php
// Points will require a nanosecond precision (this is default as per influxdb standard)
$newPoints = $database->writePoints($points);

// Points will require second precision
$newPoints = $database->writePoints($points, Database::PRECISION_SECONDS);

// Points will require microsecond precision
$newPoints = $database->writePoints($points, Database::PRECISION_MICROSECONDS);
```

Please note that `exec('date + %s%N')` does NOT work under MacOS; you can use PHP's `microtime` to get a timestamp with microsecond precision, like such:

```php
list($usec, $sec) = explode(' ', microtime());
$timestamp = sprintf('%d%06d', $sec, $usec*1000000);
```

### Creating databases

When creating a database a default retention policy is added. This retention policy does not have a duration
so the data will be flushed with the memory.

This library makes it easy to provide a retention policy when creating a database:

```php
// create the client
$client = new \InfluxDB\Client($host, $port, '', '');

// select the database
$database = $client->selectDB('influx_test_db');

// create the database with a retention policy
$result = $database->create(new RetentionPolicy('test', '5d', 1, true));

// check if a database exists then create it if it doesn't
$database = $client->selectDB('test_db');

if (!$database->exists()) {
	$database->create(new RetentionPolicy('test', '1d', 2, true));
}
```

You can also alter retention policies:

```php
$database->alterRetentionPolicy(new RetentionPolicy('test', '2d', 5, true));
```

and list them:

```php
$result = $database->listRetentionPolicies();
```

You can add more retention policies to a database:

```php
$result = $database->createRetentionPolicy(new RetentionPolicy('test2', '30d', 1, true));
```

### Client functions

Some functions are too general for a database. So these are available in the client:

```php
// list users
$result = $client->listUsers();

// list databases
$result = $client->listDatabases();
```

### Admin functionality

You can use the client's $client->admin functionality to administer InfluxDB via the API.

```php
// add a new user without privileges
$client->admin->createUser('testuser123', 'testpassword');

// add a new user with ALL cluster-wide privileges
$client->admin->createUser('admin_user', 'password', \InfluxDB\Client\Admin::PRIVILEGE_ALL);

// drop user testuser123
$client->admin->dropUser('testuser123');
```

List all the users:

```php
// show a list of all users
$results = $client->admin->showUsers();

// show users returns a ResultSet object
$users = $results->getPoints();
```

#### Granting and revoking privileges

Granting permissions can be done on both the database level and cluster-wide.
To grant a user specific privileges on a database, provide a database object or a database name.

```php
// grant permissions using a database object
$database = $client->selectDB('test_db');
$client->admin->grant(\InfluxDB\Client\Admin::PRIVILEGE_READ, 'testuser123', $database);

// give user testuser123 read privileges on database test_db
$client->admin->grant(\InfluxDB\Client\Admin::PRIVILEGE_READ, 'testuser123', 'test_db');

// revoke user testuser123's read privileges on database test_db
$client->admin->revoke(\InfluxDB\Client\Admin::PRIVILEGE_READ, 'testuser123', 'test_db');

// grant a user cluster-wide privileges
$client->admin->grant(\InfluxDB\Client\Admin::PRIVILEGE_READ, 'testuser123');

// Revoke an admin's cluster-wide privileges
$client->admin->revoke(\InfluxDB\Client\Admin::PRIVILEGE_ALL, 'admin_user');
```

## Todo

* More unit tests
* Increase documentation (wiki?)
* Add more features to the query builder
* Add validation to RetentionPolicy


## Changelog

#### 1.14.5
- Update key concepts link to point to the proper place.
- Replace costly array_merge calls with foreach + array operator
- Add getter method for verifySSL
- Support for Symfony 4

#### 1.14.3
* Deprecate IF NOT EXISTS clause in database creation

#### 1.14.2
* Fix Notice when calling InfluxDB\Client::fromDSN without username or password
* fixed Guzzle client timeout is float
* Fix annotation
* Remove unused property
* Fixed misspelling
* Fixed tag with Boolean/Null value trigger parse error

#### 1.4.1
* Fixed bug: Escape field values as per line protocol. 

#### 1.4.0
* Updating Influx Database with support for writing direct payloads, thanks @virgofx 

#### 1.3.1
* Added ability to write data to a specific retention policy, thanks @virgofx !

#### 1.3.0
* Added quoting of dbname in queries
* Added orderBy to query builder
* Fixed wrong orderby tests
* Travis container-infra and php 7

#### 1.2.2
* Fixed issue with listUsers() method
* Added more unit tests
* Added getColumns method to \InfluxDB\ResultSet

#### 1.2.0
* Added support for 32 bit systems
* Added setters/getters for Point fields

#### 1.1.3
* Added support for symfony3

#### 1.1.2
* Fixed issue with authentication when writing data

#### 1.1.1
* Added support for 0.9.4
* Added if not exists support to database->create()
* Added getLastQuery method

#### 1.1.0
* Added support for 0.9.3 rc2
* Changed the way we handle the datatypes of values
* Changed list retention policies to reflect the changes in 0.9.3

#### 1.0.1
* Added support for authentication in the guzzle driver
* Added admin functionality

#### 1.0.0
* -BREAKING CHANGE- Dropped support for PHP 5.3 and PHP 5.4
* Allowing for custom drivers
* UDP support

#### 0.1.2
* Added exists method to Database class
* Added time precision to database class

#### 0.1.1
* Merged repository to influxdb/influxdb-php
* Added unit test for createRetentionPolicy
* -BREAKING CHANGE- changed $client->db to $client->selectDB

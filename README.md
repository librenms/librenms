# influxdb-php
## InfluxDB client library for PHP
[![Build Status](https://travis-ci.org/LeaseWeb/influxdb-php.svg?branch=master)](https://travis-ci.org/LeaseWeb/influxdb-php)
[![Code Climate](https://codeclimate.com/github/LeaseWeb/influxdb-php/badges/gpa.svg)](https://codeclimate.com/github/LeaseWeb/influxdb-php)
[![Test Coverage](https://codeclimate.com/github/LeaseWeb/influxdb-php/badges/coverage.svg)](https://codeclimate.com/github/LeaseWeb/influxdb-php/coverage)

### Overview

This library was created to have php port of the python influxdb client. 
This way there will be a common abstraction library between different programming languages.

### Installation

Installation can be done with composer:

composer require influxdb/influxdb-php:dev-master

### Getting started

Initialize a new client object:

```php

$client = new InfluxDB\Client($host, $port);


```

This will create a new client object which you can use to read and write points to InfluxDB.

It's also possible to create a client from a DSN:

```php
    
    // directly get the database object
    $database = InfluxDB\Client::fromDSN(sprintf('influxdb://user:pass@%s:%s/%s', $host, $port, $dbname));
    
    // get the client to retrieve other databases
    $client = $database->getClient();   
    
```

### Reading

To fetch records from InfluxDB you can do a query directly on a database:

```php
    
    // fetch the selectDB
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
         ->getQuery();
         
```

### Writing data

Writing data is done by providing an array of points to the writePoints method on a database:

```php

    $newPoints = $database->writePoints(
        array(
            new Point(
                'test_metric',
                0.64,
                array('host' => 'server01', 'region' => 'us-west'),
                array('cpucount' => 10),
                1435255849
            ),
            new Point(
                'test_metric',
                0.84,
                array('host' => 'server01', 'region' => 'us-west'),
                array('cpucount' => 10),
                1435255850
            )
        )
    );
    
```

The name of a measurement and the value are mandatory. Additional fields, tags and a timestamp are optional.
InfluxDB takes the current time as the default timestamp.

It's possible to add multiple [fields](https://influxdb.com/docs/v0.9/concepts/key_concepts.html) when writing
measurements to InfluxDB. The point class allows one to easily write data in batches to influxDB.

### Creating databases

When creating a database a default retention policy is added. This retention policy does not have a duration
so the data will be flushed with the memory. 

This library makes it easy to provide a retention policy when creating a database:

```php
    
    // create the client
    $client = new \InfluxDB\Client($host, $port, '', '');

    // select the selectDB
    $database = $client->selectDB('influx_test_db');

    // create the database with a retention policy
    $result = $database->create(new RetentionPolicy('test', '5d', 1, true));    
    
```

You can also alter retention policies:

```php
    $database->alterRetentionPolicy(new RetentionPolicy('test', '2d', 5, true));
```

and list them:

```php
    $result = $database->listRetentionPolicies();
```

### Client functions

Some functions are too general for a database. So these are available in the client:

```php

    // list users
    $result = $client->listUsers();
    
    // list databases
    $result = $client->listDatabases();
```

## Todo

* Add UDP support
* Add more admin features
* More unit tests
* Increase documentation (wiki?)
* Add more features to the query builder
* Add validation to RetentionPolicy


## Changelog

####0.1.1
* Merged repository to influxdb/influxdb-php
* Added unit test for createRetentionPolicy
* -BREAKING CHANGE- changed $client->db to $client->selectDB

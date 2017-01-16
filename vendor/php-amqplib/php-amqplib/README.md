# php-amqplib #

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This library is a _pure PHP_ implementation of the AMQP protocol. It's been tested against [RabbitMQ](http://www.rabbitmq.com/).

**Requirements: PHP 5.3** due to the use of `namespaces`.

The library was used for the PHP examples of [RabbitMQ in Action](http://manning.com/videla/) and the [official RabbitMQ tutorials](http://www.rabbitmq.com/tutorials/tutorial-one-php.html).

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

## New Maintainers (02/10/2016) ##

Thanks to [videlalvaro](https://github.com/videlalvaro) for his hard work in maintaining php-amqplib!  He's done a fantastic job
and has left big shoes to fill.

The package will now be maintained by [postalservice14](https://github.com/postalservice14) and [nubeiro](https://github.com/nubeiro).

## Supported RabbitMQ Versions ##

Starting with version 2.0 this library uses `AMQP 0.9.1` by default and thus requires [RabbitMQ 2.0 or later version](http://www.rabbitmq.com/download.html).
You shouldn't need to change your code, but test before upgrading.

## Supported RabbitMQ Extensions ##

Since the library uses `AMQP 0.9.1` we added support for the following RabbitMQ extensions:

* Exchange to Exchange Bindings
* Basic Nack
* Publisher Confirms
* Consumer Cancel Notify

Extensions that modify existing methods like `alternate exchanges` are also supported.

## Setup ##

 Add a `composer.json` file to your project:

```javascript
{
  "require": {
      "php-amqplib/php-amqplib": "2.6.*"
  }
}
```

Then provided you have [composer](http://getcomposer.org) installed, you can run the following command:

```bash
$ composer.phar install
```

That will fetch the library and its dependencies inside your vendor folder. Then you can add the following to your
.php files in order to use the library

```php
require_once __DIR__.'/vendor/autoload.php';
```

Then you need to `use` the relevant classes, for example:

```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
```

## Usage ##

With RabbitMQ running open two Terminals and on the first one execute the following commands to start the consumer:

```bash
$ cd php-amqplib/demo
$ php amqp_consumer.php
```

Then on the other Terminal do:

```bash
$ cd php-amqplib/demo
$ php amqp_publisher.php some text to publish
```

You should see the message arriving to the process on the other Terminal

Then to stop the consumer, send to it the `quit` message:

```bash
$ php amqp_publisher.php quit
```

If you need to listen to the sockets used to connect to RabbitMQ then see the example in the non blocking consumer.

```bash
$ php amqp_consumer_non_blocking.php
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Tutorials ##

To not repeat ourselves, if you want to learn more about this library,
please refer to the [official RabbitMQ tutorials](http://www.rabbitmq.com/tutorials/tutorial-one-php.html).

## More Examples ##

- `amqp_ha_consumer.php`: demos the use of mirrored queues
- `amqp_consumer_exclusive.php` and `amqp_publisher_exclusive.php`: demos fanout exchanges using exclusive queues.
- `amqp_consumer_fanout_{1,2}.php` and `amqp_publisher_fanout.php`: demos fanout exchanges with named queues.
- `basic_get.php`: demos obtaining messages from the queues by using the _basic get_ AMQP call.

## Batch Publishing ##

Let's say you have a process that generates a bunch of messages that are going to be published to the same `exchange` using the same `routing_key` and options like `mandatory`.
Then you could make use of the `batch_basic_publish` library feature. You can batch messages like this:

```php
$msg = new AMQPMessage($msg_body);
$ch->batch_basic_publish($msg, $exchange);

$msg2 = new AMQPMessage($msg_body);
$ch->batch_basic_publish($msg2, $exchange);
```

and then send the batch like this:

```php
$ch->publish_batch();
```

### When do we publish the message batch? ###

Let's say our program needs to read from a file and then publish one message per line. Depending on the message size, you will have to decide when it's better to send the batch.
You could send it every 50 messages, or every hundred. That's up to you.

## Optimized Message Publishing ##

Another way to speed up your message publishing is by reusing the `AMQPMessage` message instances. You can create your new message like this:

```
$properties = array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT);
$msg = new AMQPMessage($body, $properties);
$ch->basic_publish($msg, $exchange);
```

Now let's say that while you want to change the message body for future messages, you will keep the same properties, that is, your messages will still be `text/plain` and the `delivery_mode` will still be `AMQPMessage::DELIVERY_MODE_PERSISTENT`. If you create a new `AMQPMessage` instance for every published message, then those properties would have to be re-encoded in the AMQP binary format. You could avoid all that by just reusing the `AMQPMessage` and then resetting the message body like this:

```php
$msg->setBody($body2);
$ch->basic_publish($msg, $exchange);
```

## Truncating Large Messages ##

AMQP imposes no limit on the size of messages; if a very large message is received by a consumer, PHP's memory limit may be reached
within the library before the callback passed to `basic_consume` is called.

To avoid this, you can call the method `AMQPChannel::setBodySizeLimit(int $bytes)` on your Channel instance. Body sizes exceeding this limit will be truncated,
and delivered to your callback with a `AMQPMessage::$is_truncated` flag set to `true`. The property `AMQPMessage::$body_size` will reflect the true body size of
a received message, which will be higher than `strlen(AMQPMessage::getBody())` if the message has been truncated.

Note that all data above the limit is read from the AMQP Channel and immediately discarded, so there is no way to retrieve it within your
callback. If you have another consumer which can handle messages with larger payloads, you can use `basic_reject` or `basic_nack` to tell
the server (which still has a complete copy) to forward it to a Dead Letter Exchange.

By default, no truncation will occur. To disable truncation on a Channel that has had it enabled, pass `0` (or `null`) to `AMQPChannel::setBodySizeLimit()`.

##UNIX Signals##

If you have installed [PCNTL extension](http://www.php.net/manual/en/book.pcntl.php) dispatching of signal will be handled when consumer is not processing message.

```php
$pcntlHandler = function ($signal) {
    switch ($signal) {
        case \SIGTERM:
        case \SIGUSR1:
        case \SIGINT:
            // some stuff before stop consumer e.g. delete lock etc
            exit(0);
            break;
        case \SIGHUP:
            // some stuff to restart consumer
            break;
        default:
            // do nothing
    }
};

declare(ticks = 1) {
    pcntl_signal(\SIGTERM, $pcntlHandler);
    pcntl_signal(\SIGINT,  $pcntlHandler);
    pcntl_signal(\SIGUSR1, $pcntlHandler);
    pcntl_signal(\SIGHUP,  $pcntlHandler);
}
```

To disable this feature just define constant `AMQP_WITHOUT_SIGNALS` as `true`

```php
<?php
define('AMQP_WITHOUT_SIGNALS', true);

... more code

```

## Debugging ##

If you want to know what's going on at a protocol level then add the following constant to your code:

```php
<?php
define('AMQP_DEBUG', true);

... more code

?>
```

## Benchmarks ##

To run the publishing/consume benchmark type:

```bash
$ make benchmark
```

## Tests ##

To successfully run the tests you need to first setup the test `user` and test `virtual host`.

You can do that by running the following commands after starting RabbitMQ:

```bash
$ rabbitmqctl add_vhost phpamqplib_testbed
$ rabbitmqctl add_user phpamqplib phpamqplib_password
$ rabbitmqctl set_permissions -p phpamqplib_testbed phpamqplib ".*" ".*" ".*"
```

Once your environment is set up you can run your tests like this:

```bash
$ make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Using AMQP 0.8 ##

If you still want to use the old version of the protocol then you can do it by settings the following constant in your configuration code:

```php
define('AMQP_PROTOCOL', '0.8');
```

The default value is `'0.9.1'`.

## Providing your own autoloader ##

If for some reason you don't want to use composer, then you need to have an autoloader in place fo the library classes. People have [reported](https://github.com/videlalvaro/php-amqplib/issues/61#issuecomment-37855050) to use this [autoloader](https://gist.github.com/jwage/221634) with success.

## Original README: ##

Below is the original README file content. Credits goes to the original authors.

PHP library implementing Advanced Message Queuing Protocol (AMQP).

The library is port of python code of py-amqplib
http://barryp.org/software/py-amqplib/

It have been tested with RabbitMQ server.

Project home page: http://code.google.com/p/php-amqplib/

For discussion, please join the group:

http://groups.google.com/group/php-amqplib-devel

For bug reports, please use bug tracking system at the project page.

Patches are very welcome!

Author: Vadim Zaliva <lord@crocodile.org>

[ico-version]: https://img.shields.io/packagist/v/php-amqplib/php-amqplib.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-LGPL-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/php-amqplib/php-amqplib/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/php-amqplib/php-amqplib.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/php-amqplib/php-amqplib.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/php-amqplib/php-amqplib.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/php-amqplib/php-amqplib
[link-travis]: https://travis-ci.org/php-amqplib/php-amqplib
[link-scrutinizer]: https://scrutinizer-ci.com/g/php-amqplib/php-amqplib/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/php-amqplib/php-amqplib
[link-downloads]: https://packagist.org/packages/php-amqplib/php-amqplib
[link-author]: https://github.com/php-amqplib
[link-contributors]: ../../contributors

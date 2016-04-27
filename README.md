# Thumper

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Thumper is a PHP library that aims to abstract several messaging patterns that can be implemented over RabbitMQ.

Inside the _examples_ folder you can see how to implement RPC, parallel processing, simple queue servers and pub/sub.

## Install

Via Composer

``` bash
$ composer require php-amqplib/thumper
```

## About the Examples

Each example has a README.md file that shows how to execute it. All the examples expect that RabbitMQ is running. They have been tested using RabbitMQ 2.1.1

For example, to publish message to RabbitMQ is as simple as this:

		$producer = new Thumper\Producer($connection);
		$producer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
		$producer->publish($argv[1]);

And then to consume them on the other side of the wire:

		$myConsumer = function($msg)
		{
		  echo $msg, "\n";
		};

		$consumer = new Thumper\Consumer($connection);
		$consumer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
		$consumer->setQueueOptions(array('name' => 'hello-queue'));
		$consumer->setCallback($myConsumer); //myConsumer could be any valid PHP callback
		$consumer->consume(5); //5 is the number of messages to consume.

### Queue Server

This example illustrates how to create a producer that will publish jobs into a queue. Those jobs will be processed later by a consumer –or several of them–.

### RPC

This example illustrates how to do RPC over RabbitMQ. We have a RPC Client that will send request to a server that returns the number of characters in the provided strings. The server code is inside the _parallel\_processing_ folder.

### Parallel Processing

This example is based on the RPC one. In this case it shows how to achieve parallel execution with PHP. Let's say that you have to execute two expensive tasks. One takes 5 seconds and the other 10. Instead of waiting 15 seconds, we can send the requests in parallel and then wait for the replies which should take 10 seconds now –the time of the slowest task–.

### Topic

In this case we can see how to achieve publish/subscribe with RabbitMQ. The example is about logging. We can log with several levels and subjects and then have consumers that listen to different log levels act accordingly.

### Anonymous Consumers

Also inside the _topic_ folder there's an anonymous consumer example. The idea here is for those situations when you need to hook up a queue to some exchange to "spy" what's going on, but when you quit your program you want that the queue is automatically deleted. We can achieve this using an unnamed queue.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Disclaimer

This code is experimental. The idea is to show how easy is to implement such patterns with RabbitMQ and AMQP.

## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/php-amqplib/thumper.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/php-amqplib/Thumper/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/php-amqplib/Thumper.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/php-amqplib/Thumper.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/php-amqplib/thumper.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/php-amqplib/Thumper
[link-travis]: https://travis-ci.org/php-amqplib/Thumper
[link-scrutinizer]: https://scrutinizer-ci.com/g/php-amqplib/Thumper/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/php-amqplib/Thumper
[link-downloads]: https://packagist.org/packages/php-amqplib/thumper
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors

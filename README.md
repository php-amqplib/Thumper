# Thumper #

Thumper is a PHP library that aims to abstract several messaging patterns that can be implemented over RabbitMQ.

Inside the _examples_ folder you can see how to implement RPC, parallel processing, simple queue servers and pub/sub.

# About the Examples #

Each example has a README.md file that shows how to execute it. All the examples expect that RabbitMQ is running. They have been tested using RabbitMQ 2.1.1

## Queue Server ##

This example illustrates how to create a producer that will publish jobs into a queue. Those jobs will be processed later by a consumer –or several of them–.

## RPC ##

This example illustrates how to do RPC over RabbitMQ. We have a RPC Client that will send request to a server that returns the number of characters in the provided strings. The server code is inside the _parallel\_processing_ folder.

## Parallel Processing ##

This example is based on the RPC one. In this case it shows how to achieve parallel execution with PHP. Let's say that you have to execute two expensive tasks. One takes 5 seconds and the other 10. Instead of waiting 15 seconds, we can send the requests in parallel and then wait for the replies which should take 10 seconds now –the time of the slowest task–.

## Topic ##

In this case we can see how to achieve publish/subscribe with RabbitMQ. The example is about logging. We can log with several levels and subjects and then have consumers that listen to different log levels and act accordingly.

# Disclaimer #

This code is experimental. The idea is to show how easy is to implement such patterns with RabbitMQ and AMQP.

# License #

See LICENSE.md
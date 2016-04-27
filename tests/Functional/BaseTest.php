<?php

namespace Thumper\Test\Functional;

use PhpAmqpLib\Connection\AMQPSocketConnection;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPSocketConnection
     */
    protected $connection;

    public function setUp()
    {
        $this->connection = new AMQPSocketConnection('localhost', 5672, 'guest', 'guest');
    }
}

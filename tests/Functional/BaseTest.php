<?php

namespace Thumper\Test\Functional;

use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    public function setUp()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }
}

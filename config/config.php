<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPLazyConnection;
use Thumper\ConnectionRegistry;

$connections = array(
    'default' => new AMQPLazyConnection('localhost', 5672, 'guest', 'guest', '/')
);

$registry = new ConnectionRegistry($connections, 'default');

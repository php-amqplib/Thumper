<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$connections = array(
    'default' => new \PhpAmqpLib\Connection\AMQPLazyConnection('localhost', 5672, 'guest', 'guest', '/')
);

$registry = new \Thumper\ConnectionRegistry($connections, 'default');
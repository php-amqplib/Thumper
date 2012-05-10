<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$producer = new Producer(HOST, PORT, USER, PASS, VHOST);
$producer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$producer->publish($argv[1], sprintf('%s.%s', $argv[2], $argv[3]));

?>
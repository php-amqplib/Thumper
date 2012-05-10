<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/Producer.php');

$producer = new Thumper\Producer(HOST, PORT, USER, PASS, VHOST);
$producer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$producer->publish($argv[1], sprintf('%s.%s', $argv[2], $argv[3]));

?>
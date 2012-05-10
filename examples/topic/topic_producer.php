<?php

require_once('../../config/autoload.php');
require_once('../../config/config.php');
require_once('../../lib/Thumper/Producer.php');

$producer = new Producer(HOST, PORT, USER, PASS, VHOST);
$producer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$producer->publish($argv[1], sprintf('%s.%s', $argv[2], $argv[3]));

?>
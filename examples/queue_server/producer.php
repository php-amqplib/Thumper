<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/Producer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$producer = new Producer(HOST, PORT, USER, PASS, VHOST);
$producer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
$producer->publish($argv[1]); //The first argument will be the published message

?>
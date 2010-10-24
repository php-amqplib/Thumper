<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/AnonConsumer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new AnonConsumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$consumer->setRoutingKey($argv[1]);
$consumer->setCallback($myConsumer);
$consumer->consume(5);

?>
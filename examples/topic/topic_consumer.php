<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/Consumer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new Consumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$consumer->setQueueOptions(array('name' => $argv[2] . '-queue'));
$consumer->setRoutingKey($argv[1]);
$consumer->setCallback($myConsumer);
$consumer->consume(5);

?>
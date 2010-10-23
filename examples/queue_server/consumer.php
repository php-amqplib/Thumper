<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/Consumer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new Consumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
$consumer->setQueueOptions(array('name' => 'hello-queue'));
$consumer->setCallback($myConsumer);
$consumer->consume(5);

?>
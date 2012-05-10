<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/Consumer.php');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new Consumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
$consumer->setQueueOptions(array('name' => 'hello-queue'));
$consumer->setCallback($myConsumer); //myConsumer could be any valid PHP callback
$consumer->consume(5); //5 is the number of messages to consume

?>
<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

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
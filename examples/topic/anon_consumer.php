<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new Thumper\AnonConsumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$consumer->setRoutingKey($argv[1]);
$consumer->setCallback($myConsumer);
$consumer->consume(5);

?>
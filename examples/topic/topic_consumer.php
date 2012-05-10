<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/Consumer.php');

$myConsumer = function($msg)
{
  echo $msg, "\n";
};

$consumer = new Thumper\Consumer(HOST, PORT, USER, PASS, VHOST);
$consumer->setExchangeOptions(array('name' => 'logs-exchange', 'type' => 'topic'));
$consumer->setQueueOptions(array('name' => $argv[2] . '-queue'));
$consumer->setRoutingKey($argv[1]);
$consumer->setCallback($myConsumer);
$consumer->consume(5);

?>
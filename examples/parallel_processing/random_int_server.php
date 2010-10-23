<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/RpcServer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$randomInt = function($data)
{
  sleep(5);
  $data = unserialize($data);
  return rand($data['min'], $data['max']);
};

$server = new RpcServer(HOST, PORT, USER, PASS, VHOST);
$server->initServer('random-int');
$server->setCallback($randomInt);
$server->start();

?>
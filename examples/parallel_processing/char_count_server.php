<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/RpcServer.php');
require_once('../../lib/php-amqplib/amqp.inc');

$charCount = function($word)
{
  sleep(2);
  return strlen($word);
};

$server = new RpcServer(HOST, PORT, USER, PASS, VHOST);
$server->initServer('charcount');
$server->setCallback($charCount);
$server->start();

?>
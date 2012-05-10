<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/RpcServer.php');

$charCount = function($word)
{
  sleep(2);
  return strlen($word);
};

$server = new Thumper\RpcServer(HOST, PORT, USER, PASS, VHOST);
$server->initServer('charcount');
$server->setCallback($charCount);
$server->start();

?>
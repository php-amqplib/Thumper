<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$charCount = function($word)
{
  sleep(2);
  return strlen($word);
};

$server = new Thumper\RpcServer(HOST, PORT, USER, PASS, VHOST);
$server->initServer('charcount');
$server->setCallback($charCount);
$server->start();


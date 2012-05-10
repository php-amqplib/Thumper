<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$randomInt = function($data)
{
  sleep(5);
  $data = unserialize($data);
  return rand($data['min'], $data['max']);
};

$server = new Thumper\RpcServer(HOST, PORT, USER, PASS, VHOST);
$server->initServer('random-int');
$server->setCallback($randomInt);
$server->start();


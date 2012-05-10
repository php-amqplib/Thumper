<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$producer = new Thumper\Producer(HOST, PORT, USER, PASS, VHOST);
$producer->setExchangeOptions(array('name' => 'hello-exchange', 'type' => 'direct'));
$producer->publish($argv[1]); //The first argument will be the published message

?>
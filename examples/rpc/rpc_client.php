<?php

require_once(dirname(dirname(__DIR__)) . '/config/config.php');

$client = new Thumper\RpcClient(HOST, PORT, USER, PASS, VHOST);
$client->initClient();
$client->addRequest($argv[1], 'charcount', 'charcount'); //the third parameter is the request identifier
echo "Waiting for replies…\n";
$replies = $client->getReplies();

var_dump($replies);


<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/RpcClient.php');

$client = new Thumper\RpcClient(HOST, PORT, USER, PASS, VHOST);
$client->initClient();
$client->addRequest($argv[1], 'charcount', 'charcount'); //the third parameter is the request identifier
echo "Waiting for replies…\n";
$replies = $client->getReplies();

var_dump($replies);

?>
<?php

require_once(__DIR__ . '/../../config/autoload.php');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../lib/Thumper/RpcClient.php');

$start = time();

$client = new RpcClient(HOST, PORT, USER, PASS, VHOST);
$client->initClient();
$client->addRequest($argv[1], 'charcount', 'charcount'); //charcount is the request identifier
$client->addRequest(serialize(array('min' => 0, 'max' => (int) $argv[2])), 'random-int', 'random-int'); //random-int is the request identifier
echo "Waiting for replies…\n";
$replies = $client->getReplies();

var_dump($replies);

echo "Total time: ", time() - $start, "\n";

?>
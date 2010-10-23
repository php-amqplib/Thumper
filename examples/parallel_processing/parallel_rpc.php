<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/RpcClient.php');
require_once('../../lib/php-amqplib/amqp.inc');

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
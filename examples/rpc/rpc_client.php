<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/RpcClient.php');
require_once('../../lib/php-amqplib/amqp.inc');

$client = new RpcClient(HOST, PORT, USER, PASS, VHOST);
$client->initClient();
$client->addRequest($argv[1], 'charcount', 'charcount'); //the third parameter is the request identifier
echo "Waiting for replies…\n";
$replies = $client->getReplies();

var_dump($replies);

?>
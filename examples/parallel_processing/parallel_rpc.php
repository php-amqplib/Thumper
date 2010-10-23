<?php

require_once('../../config/config.php');
require_once('../../lib/Thumper/RpcClient.php');
require_once('../../lib/php-amqplib/amqp.inc');

$start = time();

$client = new RpcClient(HOST, PORT, USER, PASS, VHOST);
$client->initClient();
$client->addRequest($argv[1], 'charcount-exchange', 'charcount');
$client->addRequest(serialize(array('min' => 0, 'max' => 10)), 'random-int-exchange', 'random-int');
echo "Waiting for replies…";
$replies = $client->getReplies();

var_dump($replies);

echo "Total time: ", time() - $start, "\n";

?>
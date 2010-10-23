Start the RPC servers. On one terminal window type:

		$ php char_count_server.php
		
Then start the second server in another window:

		$ php random_int_server.php
		
And on the third window launch the RPC client:

		php parallel_rpc.php 'Some Words' 15
		
The response will be:

		Waiting for replies…
		array(2) {
		  ["charcount"]=>
		  string(2) "10"
		  ["random-int"]=>
		  string(2) "13"
		}
		Total time: 5

The first argument is the string to send so we get the char count back. The second argument is the _max_ for the _rand()_ function to return a random number between 0 and _max_.

The _char\_count\_server_ will take at least 2 seconds –see the sleep() call inside–. The _random\_int\_server_ will take at least 5 seconds to reply.

The total running time of the RPC Client should be around 5 seconds –the slowest server–.

The return will be an associative _array()_ having all the responses there. The third parameter to RpcClient::addRequest() will be the request identifier. If this parameter is "char-count" then the response will have a key called "char-count" with the response from the server.
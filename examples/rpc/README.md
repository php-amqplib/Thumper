Start the _char\_count\_server_ as explained in the _parallel\_processing_ example. Then on another window launch the client like this:

		$ php rpc_client.php 'hola'

And the response will be:

		Waiting for replies…
		array(1) {
		  ["charcount"]=>
		  string(1) "4"
		}


First start the topic consumer providing the desired routing key and the queue name where to receive the logs.

		$ php topic_consumer.php 'app.info' 'info-logs'

Then send log messages like this:

		$ php topic_producer.php 'some message' app info
		$ php topic_producer.php 'some other message' app error
		
You will see that only the first message arrived to our consumer. That's because our consumer is listening to 'app.info' messages and we sent a message with the 'app.error' routing key.

Now open another window and start an error listener consumer:

		$ php topic_consumer.php 'app.error' 'error-logs'

If you send the second message again, it should arrive only to the last consumer.
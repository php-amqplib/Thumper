<?php

namespace Thumper\Test\Functional;

use Thumper\Consumer;
use Thumper\Producer;

class RpcTest extends BaseTest
{
    /**
     * @var int
     */
    public $messagesConsumed = 0;

    public function testConsumeProducedMessages()
    {
        $expectedMessagesConsumed = 5;
        $exchangeOptions = array('name' => 'hello-exchange', 'type' => 'direct');

        $producer = new Producer($this->connection);
        $producer->setExchangeOptions($exchangeOptions);
        for ($i = 0; $i < $expectedMessagesConsumed; $i++) {
            $producer->publish('foobar');
        }

        $self = $this;

        $callback = function ($message) use ($self) {
            if ($message === 'foobar') {
                $self->messagesConsumed++;
            }
        };

        $consumer = new Consumer($this->connection);
        $consumer->setExchangeOptions($exchangeOptions);
        $consumer->setQueueOptions(array('name' => 'hello-queue'));
        $consumer->setCallback($callback);
        $consumer->consume($expectedMessagesConsumed);

        $this->assertEquals($expectedMessagesConsumed, $this->messagesConsumed);
    }
}

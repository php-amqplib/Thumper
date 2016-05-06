<?php
namespace Thumper\Test;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Thumper\Consumer;

class ConsumerTest extends BaseTest
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var AMQPStreamConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConnection;

    /**
     * @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChannel;

    public function setUp()
    {
        $this->mockConnection = $this->getMockConnection();
        $this->mockChannel = $this->getMockChannel();

        $this->mockConnection
            ->expects($this->once())
            ->method('channel')
            ->willReturn($this->mockChannel);

        $this->consumer = new Consumer($this->mockConnection);
    }

    /**
     * @test
     */
    public function consumeHappyPath()
    {
        $name = uniqid('name', true);
        $queueName = uniqid('queueName', true);

        $this->consumer
            ->setExchangeOptions([
                'name' => $name,
                'type' => 'direct'
            ]);

        $this->mockChannel
            ->callbacks = ['one'];
        $this->mockChannel
            ->expects($this->atLeastOnce())
            ->method('wait')
            ->willReturnCallback(function () {
                array_pop($this->mockChannel->callbacks);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare');

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare')
            ->willReturn([$queueName, 0, 1]);
        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind');
        $this->mockChannel
            ->expects($this->once())
            ->method('basic_consume')
            ->with(
                $queueName,
                'PHPPROCESS_' . getmypid(),
                false,
                false,
                false,
                false,
                array($this->consumer, 'processMessage')
            );
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_qos');

        $this->consumer
            ->consume(1);
    }

    /**
     * @test
     * @dataProvider setUpConsumerExceptions
     * @param \Exception $exception
     */
    public function consumeWhenExchangeDeclareThrowsExceptions(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $name = uniqid('name', true);

        $this->consumer
            ->setExchangeOptions([
                'name' => $name,
                'type' => 'direct'
            ]);

        $this->mockChannel
            ->expects($this->never())
            ->method('wait');

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->willThrowException($exception);

        $this->mockChannel
            ->expects($this->never())
            ->method('queue_declare');
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_bind');
        $this->mockChannel
            ->expects($this->never())
            ->method('basic_consume');
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_qos');

        $this->consumer
            ->consume(1);
    }

    /**
     * @test
     * @dataProvider setUpConsumerExceptions
     * @param \Exception $exception
     */
    public function consumeQueueDeclareThrowsException(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $name = uniqid('name', true);

        $this->consumer
            ->setExchangeOptions([
                'name' => $name,
                'type' => 'direct'
            ]);

        $this->mockChannel
            ->callbacks = ['one'];
        $this->mockChannel
            ->expects($this->never())
            ->method('wait')
            ->willReturnCallback(function () {
                array_pop($this->mockChannel->callbacks);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare');

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare')
            ->willThrowException($exception);
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_bind');
        $this->mockChannel
            ->expects($this->never())
            ->method('basic_consume');
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_qos');

        $this->consumer
            ->consume(1);
    }

    /**
     * @test
     * @dataProvider setUpConsumerExceptions
     * @param \Exception $exception
     */
    public function consumeQueueBindThrowsExceptions(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $name = uniqid('name', true);

        $this->consumer
            ->setExchangeOptions([
                'name' => $name,
                'type' => 'direct'
            ]);

        $this->mockChannel
            ->callbacks = ['one'];
        $this->mockChannel
            ->expects($this->never())
            ->method('wait')
            ->willReturnCallback(function () {
                array_pop($this->mockChannel->callbacks);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare');

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare');
        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind')
            ->willThrowException($exception);
        $this->mockChannel
            ->expects($this->never())
            ->method('basic_consume');
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_qos');

        $this->consumer
            ->consume(1);
    }

    /**
     * @test
     * @dataProvider setUpConsumerExceptions
     * @param \Exception $exception
     */
    public function consumeBasicConsumeThrowsExceptions(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $name = uniqid('name', true);

        $this->consumer
            ->setExchangeOptions([
                'name' => $name,
                'type' => 'direct'
            ]);

        $this->mockChannel
            ->callbacks = ['one'];
        $this->mockChannel
            ->expects($this->never())
            ->method('wait')
            ->willReturnCallback(function () {
                array_pop($this->mockChannel->callbacks);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare');

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare');
        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind');
        $this->mockChannel
            ->expects($this->once())
            ->method('basic_consume')
            ->willThrowException($exception);
        $this->mockChannel
            ->expects($this->never())
            ->method('queue_qos');

        $this->consumer
            ->consume(1);
    }

    /**
     * @test
     */
    public function processMessageHappyPath()
    {
        $body = uniqid('body', true);
        $deliveryTag = uniqid('deliveryTag', true);
        $message = new AMQPMessage($body);
        $message->delivery_info['channel'] = $this->mockChannel;
        $message->delivery_info['delivery_tag'] = $deliveryTag;

        $this->consumer
            ->setCallback(function () {});

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_ack')
            ->with($deliveryTag);

        $this->mockChannel
            ->expects($this->never())
            ->method('basic_cancel');

        $this->consumer
            ->processMessage($message);
    }

    /**
     * @test
     */
    public function processMessageWhenConsumedEqualsTarget()
    {
        $body = uniqid('body', true);
        $deliveryTag = uniqid('deliveryTag', true);
        $consumerTag = uniqid('consumerTag', true);

        $message = new AMQPMessage($body);
        $message->delivery_info['channel'] = $this->mockChannel;
        $message->delivery_info['delivery_tag'] = $deliveryTag;
        $message->delivery_info['consumer_tag'] = $consumerTag;

        $this->consumer
            ->setCallback(function () {});

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_ack')
            ->with($deliveryTag);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_cancel')
            ->with($consumerTag);

        $this->setReflectionProperty($this->consumer, 'target', 1);

        $this->consumer
            ->processMessage($message);
    }

    /**
     * @test
     */
    public function setCallbackThrowsException()
    {
        $callback = uniqid('callback', true);
        $this->setExpectedException('\Exception', 'Callback ' . $callback . ' is not callable.');
        $this->consumer
            ->setCallback($callback);
    }

    public function setUpConsumerExceptions()
    {
        return [
            [new AMQPOutOfBoundsException('Out of Bounds')],
            [new AMQPRuntimeException('Runtime Exception')]
        ];
    }
}

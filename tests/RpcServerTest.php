<?php
namespace Thumper\Test;

use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Thumper\RpcServer;

class RpcServerTest extends BaseTest
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    public $mockChannel;

    /**
     * @var \PhpAmqpLib\Connection\AbstractConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConnection;

    /**
     * @var RpcServer
     */
    private $server;

    public function setUp()
    {
        $this->mockConnection = $this->getMockConnection();

        $this->mockChannel = $this->getMockChannel();
        $this->mockConnection
            ->expects($this->once())
            ->method('channel')
            ->willReturn($this->mockChannel);

        $this->server = new RpcServer($this->mockConnection);
    }

    /**
     * @test
     */
    public function constructor()
    {
        $this->assertInstanceOf('\Thumper\RpcServer', $this->server);
        $this->assertInstanceOf('\Thumper\BaseConsumer', $this->server);
        $this->assertInstanceOf('\Thumper\BaseAmqp', $this->server);
    }

    /**
     * @test
     */
    public function initServerHappyPath()
    {
        $name = uniqid('name', true);
        $this->server
            ->initServer($name);

        $exchangeOptions = $this->getReflectionPropertyValue($this->server, 'exchangeOptions');
        $this->assertEquals($name, $exchangeOptions['name']);
        $this->assertEquals('direct', $exchangeOptions['type']);

        $queueOptions = $this->getReflectionPropertyValue($this->server, 'queueOptions');
        $this->assertEquals($name, $queueOptions['name']);
    }

    /**
     * @test
     */
    public function startHappyPath()
    {
        $name = uniqid('name', true);
        $queueName = uniqid('queueName', true);
        $this->server
            ->initServer($name);

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->with($name, 'direct', false, true, false, false, null, null);

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_declare')
            ->with($name)
            ->willReturn(array($queueName, false, false));

        $this->mockChannel
            ->expects($this->once())
            ->method('queue_bind')
            ->with($queueName, $name, '', false, null, null);

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
                array($this->server, 'processMessage'),
                null,
                array()
            );

        $this->server
            ->start();
    }

    /**
     * @test
     */
    public function startWithQos()
    {
        $this->mockChannel
            ->expects($this->once())
            ->method('basic_qos')
            ->with(5, 1, true);

        $this->server
            ->setQos(
                array(
                    'prefetch_size' => 5,
                    'prefetch_count' => 1,
                    'global' => true
                )
            );

        $this->startHappyPath();
    }

    /**
     * @test
     * @param $callbacks
     * @dataProvider startChannelCallbackDataProvider
     */
    public function startWithChannelCallbacks($callbacks)
    {
        $this->mockChannel->callbacks = $callbacks;
        $self = $this;
        $this->mockChannel
            ->expects($this->atLeast(count($callbacks)))
            ->method('wait')
            ->willReturnCallback(function () use ($self) {
                array_pop($self->mockChannel->callbacks);
            });

        $this->startHappyPath();
    }

    /**
     * @test
     * @throws \Exception
     * @throws \OutOfBoundsException
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function processMessageHappyPath()
    {
        $body = uniqid('body', true);
        $deliveryTag = uniqid('deliveryTag', true);
        $replyTo = uniqid('replyTo', true);
        $correlationId = uniqid('correlationId', true);
        $result = uniqid('result', true);
        $message = new AMQPMessage($body);
        $message->delivery_info['channel'] = $this->mockChannel;
        $message->delivery_info['delivery_tag'] = $deliveryTag;
        $message->delivery_info['reply_to'] = $replyTo;
        $message->delivery_info['correlation_id'] = $correlationId;

        $callback = function () use ($result) {
            return $result;
        };
        $this->server
            ->setCallback($callback);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                static::callback(function (AMQPMessage $message) use ($result) {
                    return $message->body === $result;
                }),
                '',
                $replyTo,
                false,
                false,
                null
            );

        $this->server
            ->processMessage($message);
    }

    /**
     * @param \Exception $expectedException
     * @test
     * @dataProvider processMessagesFailuresDataProvider
     * @throws \OutOfBoundsException
     * @throws \Exception
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function processMessageFailures(\Exception $expectedException)
    {
        $replyTo = uniqid('replyTo', true);
        $body = uniqid('body', true);
        $deliveryTag = uniqid('deliveryTag', true);
        $result = uniqid('result', true);
        $correlationId = uniqid('correlationId', true);
        $this->mockChannel
            ->expects($this->at(1))
            ->method('basic_publish')
            ->willThrowException($expectedException);

        $this->mockChannel
            ->expects($this->at(2))
            ->method('basic_publish')
            ->with(
                static::callback(function (AMQPMessage $message) use ($expectedException) {
                    return $message->body === 'error: '. $expectedException->getMessage();
                }),
                '',
                $replyTo,
                false,
                false,
                null
            );

        $message = new AMQPMessage($body);
        $message->delivery_info['channel'] = $this->mockChannel;
        $message->delivery_info['reply_to'] = $replyTo;
        $message->delivery_info['delivery_tag'] = $deliveryTag;
        $message->delivery_info['correlation_id'] = $correlationId;

        $callback = function () use ($result) {
            return $result;
        };
        $this->server
            ->setCallback($callback);

        $this->server
            ->processMessage($message);
    }

    /**
     * @return array
     */
    public function startChannelCallbackDataProvider()
    {
        return array(
            '1' => array(array(1)),
            '0' => array(array()),
            '3' => array(array(1, 2, 3))
        );
    }

    /**
     * @return array
     */
    public function processMessagesFailuresDataProvider()
    {
        return array(
            array(new AMQPRuntimeException('Index Out of Bounds')),
            array(new AMQPInvalidArgumentException('Invalid Argument'))
        );
    }
}

<?php
namespace Thumper\Test;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Thumper\RpcClient;

class RpcClientTest extends BaseTest
{
    /**
     * @var RpcClient
     */
    private $client;

    /**
     * @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChannel;

    public function setUp()
    {
        $mockConnection = $this->getMockConnection();
        $this->mockChannel = $this->getMockChannel();

        $mockConnection->expects($this->once())
            ->method('channel')
            ->willReturn($this->mockChannel);

        $this->client = new RpcClient($mockConnection);
    }

    /**
     * @test
     */
    public function addRequest()
    {
        $queueName = uniqid('queueName', true);
        $message = uniqid('message', true);
        $server = uniqid('server', true);
        $requestId = uniqid('requestId', true);
        $routingKey = uniqid('routingKey', true);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($queueName, $requestId) {
                    $properties = $message->get_properties();

                    $isContentTypeCorrect = array_key_exists('content_type', $properties)
                        && $properties['content_type'] === 'text/plain';

                    $isCorrelationIdCorrect = array_key_exists('correlation_id', $properties)
                        && $properties['correlation_id'] = $requestId;

                    $isReplyToCorrect = array_key_exists('reply_to', $properties)
                        && $properties['reply_to'] === $queueName;

                    return $isContentTypeCorrect && $isCorrelationIdCorrect && $isReplyToCorrect;
                }),
                $server . '-exchange',
                $routingKey
            );

        $this->setReflectionProperty($this->client, 'queueName', $queueName);

        $this->client
            ->addRequest($message, $server, $requestId, $routingKey);

        $requests = $this->getReflectionPropertyValue($this->client, 'requests');
        $this->assertEquals(1, $requests);
    }

    /**
     * @param mixed $requestId
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must provide a request ID
     * @dataProvider requestIdDataProvider
     */
    public function addRequestWithInvalidRequestId($requestId)
    {
        $this->client
            ->addRequest('messageBody', 'server', $requestId);
    }

    /**
     * @test
     */
    public function initClient()
    {
        $this->mockChannel
            ->expects($this->exactly(1))
            ->method('queue_declare')
            ->with('', false, false, true, true)
            ->willReturn([
                'queueName'
            ]);

        $this->client
            ->initClient();
    }

    /**
     * @param int $requests
     * @test
     * @dataProvider getRepliesDataProvider
     */
    public function getReplies($requests)
    {
        $queueName = uniqid('queueName', true);
        $this->setReflectionProperty($this->client, 'queueName', $queueName);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_consume')
            ->with($queueName, $queueName, false, true, false, false, [$this->client, 'processMessage']);

        $this->mockChannel
            ->expects($this->exactly($requests))
            ->method('wait')
            ->with(null, false, null)
            ->willReturnCallback(function () {
                $replies = $this->getReflectionPropertyValue($this->client, 'replies');
                $replies[] = 'reply';
                $this->setReflectionProperty($this->client, 'replies', $replies);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_cancel')
            ->with($queueName);

        $this->setReflectionProperty($this->client, 'requests', $requests);

        $replies = $this->client
            ->getReplies();

        $this->assertEquals($requests, count($replies));
    }

    /**
     * @test
     */
    public function processMessage()
    {
        $body = uniqid('body', true);
        $correlationId = uniqid('correlationid', true);
        $mockMessage = new AMQPMessage($body, ['correlation_id' => $correlationId]);

        $this->client
            ->processMessage($mockMessage);

        $replies = $this->getReflectionPropertyValue($this->client, 'replies');
        $this->assertEquals([$correlationId => $body], $replies);
    }

    /**
     * @test
     */
    public function setTimeout()
    {
        $timeout = mt_rand();
        $this->client
            ->setTimeout($timeout);

        $requestTimeout = $this->getReflectionPropertyValue($this->client, 'requestTimeout');
        $this->assertEquals($timeout, $requestTimeout);
    }

    /**
     * @test
     */
    public function setExchangeOptionsHappyPath()
    {
        $test = uniqid('test', true);
        $name = uniqid('name', true);
        $type = uniqid('type', true);
        $this->client
            ->setExchangeOptions(
                [
                    'name' => $name,
                    'type' => $type,
                    'internal' => true,
                    'test' => $test
                ]
            );

        $exchangeOptions = $this->getReflectionPropertyValue($this->client, 'exchangeOptions');

        $this->assertArrayHasKey('test', $exchangeOptions);
        $this->assertEquals($test, $exchangeOptions['test']);

        $this->assertEquals($name, $exchangeOptions['name']);
        $this->assertEquals($name, $exchangeOptions['name']);
        $this->assertEquals(true, $exchangeOptions['internal']);
        $this->assertEquals(false, $exchangeOptions['passive']);
    }

    /**
     * @test
     * @param $key
     * @param array $options
     * @dataProvider setExchangeOptionsExceptionDataProvider
     */
    public function setExchangeOptionsThrowsExceptions($key, $options)
    {
        $this->setExpectedException('\InvalidArgumentException', 'You must provide an exchange ' . $key);

        $this->client
            ->setExchangeOptions($options);
    }

    /**
     * @test
     */
    public function setQueueOptions()
    {
        $name = uniqid('name', true);
        $test = uniqid('test', true);
        $queueOptions = [
            'name' => $name,
            'test' => $test
        ];

        $this->client
            ->setQueueOptions($queueOptions);

        $queueOptionsValue = $this->getReflectionPropertyValue($this->client, 'queueOptions');

        $this->assertArrayHasKey('test', $queueOptionsValue);
        $this->assertEquals($test, $queueOptionsValue['test']);

        $this->assertEquals($name, $queueOptionsValue['name']);
        $this->assertFalse($queueOptionsValue['passive']);
        $this->assertNull($queueOptionsValue['ticket']);
    }

    /**
     * @test
     */
    public function setRoutingKey()
    {
        $routingKey = uniqid('routingKey', true);

        $this->client
            ->setRoutingKey($routingKey);

        $routingKeyValue = $this->getReflectionPropertyValue($this->client, 'routingKey');

        $this->assertEquals($routingKey, $routingKeyValue);
    }

    /**
     * @test
     */
    public function setQos()
    {
        $test = uniqid('test', true);

        $this->client
            ->setQos(['test' => $test]);

        $consumerOptions = $this->getReflectionPropertyValue($this->client, 'consumerOptions');

        $this->assertArrayHasKey('test', $consumerOptions['qos']);
        $this->assertEquals($test, $consumerOptions['qos']['test']);
    }

    /**
     * @return array
     */
    public function requestIdDataProvider()
    {
        return [
            'empty string' => [''],
            'false' => [false],
            'null' => [null],
            '0' => [0]
        ];
    }

    /**
     * @return array
     */
    public function getRepliesDataProvider()
    {
        return [
            [0],
            [1],
            [2]
        ];
    }

    public function setExchangeOptionsExceptionDataProvider()
    {
        return [
            [
                'name',
                []
            ],
            [
                'name',
                ['type' => 'type']
            ],
            [
                'name',
                ['name' => '']
            ],
            [
                'name',
                ['name' => false]
            ],
            [
                'name',
                ['name' => 0]
            ],
            [
                'type',
                ['name' => 'name']
            ],
            [
                'type',
                [
                    'name' => 'name',
                    'type' => false
                ]
            ],
            [
                'type',
                [
                    'name' => 'name',
                    'type' => 0
                ]
            ],
            [
                'type',
                [
                    'name' => 'name',
                    'type' => ''
                ]
            ]
        ];
    }

}

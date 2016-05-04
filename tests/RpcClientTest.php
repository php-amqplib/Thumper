<?php
namespace Thumper\Test;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Thumper\RpcClient;

class RpcClientTest extends \PHPUnit_Framework_TestCase
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
        $this->client = new RpcClient($this->getMockConnection());
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

        $this->setClientProperty('queueName', $queueName);

        $this->client
            ->addRequest($message, $server, $requestId, $routingKey);

        $reflectionClass = $this->getClientReflection();
        $requests = $reflectionClass->getProperty('requests');
        $requests->setAccessible(true);

        $this->assertEquals(1, $requests->getValue($this->client));
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
        $this->setClientProperty('queueName', $queueName);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_consume')
            ->with($queueName, $queueName, false, true, false, false, [$this->client, 'processMessage']);

        $this->mockChannel
            ->expects($this->exactly($requests))
            ->method('wait')
            ->with(null, false, null)
            ->willReturnCallback(function() {
                $repliesProperty = $this->getClientProperty('replies');

                $repliesValue = $repliesProperty->getValue($this->client);
                $repliesValue[] = 'reply';
                $repliesProperty->setValue($this->client, $repliesValue);
            });

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_cancel')
            ->with($queueName);

        $this->setClientProperty('requests', $requests);

        $replies = $this->client
            ->getReplies();

        $this->assertEquals($requests, count($replies));
    }

    /**
     * @return \PhpAmqpLib\Connection\AbstractConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConnection()
    {
        $mockConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AbstractConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockConnection->expects($this->once())
            ->method('channel')
            ->willReturn($this->mockChannel);

        return $mockConnection;
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

        $repliesProperty = $this->getClientProperty('replies');
        $replies = $repliesProperty->getValue($this->client);

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

        $requestTimeoutProperty = $this->getClientProperty('requestTimeout');
        $this->assertEquals($timeout, $requestTimeoutProperty->getValue($this->client));
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

    /**
     * @return \ReflectionClass
     */
    private function getClientReflection()
    {
        static $reflection;

        if ($reflection !== null) {
            return $reflection;
        }

        $reflection = new \ReflectionClass($this->client);
        return $reflection;
    }

    /**
     * @param $name
     * @param $value
     */
    private function setClientProperty($name, $value)
    {
        $requestsProperty = $this->getClientProperty($name);
        $requestsProperty->setValue($this->client, $value);
    }

    /**
     * @param $name
     * @return \ReflectionProperty
     */
    private function getClientProperty($name)
    {
        $reflectionClass = $this->getClientReflection();
        $requestsProperty = $reflectionClass->getProperty($name);
        $requestsProperty->setAccessible(true);
        return $requestsProperty;
    }
}

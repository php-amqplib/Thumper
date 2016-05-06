<?php
namespace Thumper\Test;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPOutOfBoundsException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Thumper\Producer;

class ProducerTest extends BaseTest
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var \PhpAmqpLib\Connection\AbstractConnection|\PHPUnit_Framework_MockObject_MockObject
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

        $this->producer = new Producer($this->mockConnection);
    }

    /**
     * @test
     * @throws \Thumper\Exception\Exception
     */
    public function publishHappyPath()
    {
        $body = uniqid('body', true);
        $exchangeName = uniqid('exchangeName', true);
        $exchangeType = uniqid('exchangeType', true);
        
        $this->producer
            ->setExchangeOptions([
                'name' => $exchangeName,
                'type' => $exchangeType
            ]);

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->with($exchangeName, $exchangeType, false, true, false);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($body) {
                    return $message->getBody() === $body;
                }),
                $exchangeName,
                ''
            );
        
        $this->producer
            ->publish($body);
    }

    /**
     * @param \Exception $exception
     * @param string $expectedMessage
     * @test
     * @dataProvider declareExchangeDataProvider
     * @throws \Thumper\Exception\Exception
     */
    public function publishThrowsExceptionWhenDeclaringExchange(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $body = uniqid('body', true);
        $exchangeName = uniqid('exchangeName', true);
        $exchangeType = uniqid('exchangeType', true);

        $this->producer
            ->setExchangeOptions([
                'name' => $exchangeName,
                'type' => $exchangeType
            ]);

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->with($exchangeName, $exchangeType, false, true, false)
            ->willThrowException($exception);

        $this->mockChannel
            ->expects($this->never())
            ->method('basic_publish');

        $this->producer
            ->publish($body);
    }

    /**
     * @param \Exception $exception
     * @test
     * @dataProvider publishMessageExceptionDataProvider
     * @throws \Thumper\Exception\Exception
     */
    public function publishThrowsExceptionWhenPublishingMessage(\Exception $exception)
    {
        $this->setExpectedException(get_class($exception), $exception->getMessage());

        $body = uniqid('body', true);
        $exchangeName = uniqid('exchangeName', true);
        $exchangeType = uniqid('exchangeType', true);

        $this->producer
            ->setExchangeOptions([
                'name' => $exchangeName,
                'type' => $exchangeType
            ]);

        $this->mockChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->with($exchangeName, $exchangeType, false, true, false);

        $this->mockChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($body) {
                    return $message->getBody() === $body;
                }),
                $exchangeName,
                ''
            )
            ->willThrowException($exception);

        $this->producer
            ->publish($body);
    }

    /**
     * @return array
     */
    public function declareExchangeDataProvider()
    {
        return [
            [new AMQPOutOfBoundsException('Out of Bounds')],
            [new AMQPRuntimeException('Runtime Exception')]
        ];
    }

    public function publishMessageExceptionDataProvider()
    {
        return [
            [new AMQPInvalidArgumentException('Invalid Argument')],
            [new AMQPRuntimeException('Runtime Exception')]
        ];
    }
}

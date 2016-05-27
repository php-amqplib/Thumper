<?php
/**
 * The MIT License
 *
 * Copyright (c) 2010 Alvaro Videla
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * PHP version 5.3
 *
 * @category   Thumper
 * @package    Thumper
 * @author     Alvaro Videla
 * @copyright  2010 Alvaro Videla. All rights reserved.
 * @license    MIT http://opensource.org/licenses/MIT
 * @link       https://github.com/videlalvaro/Thumper
 */
namespace Thumper;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

abstract class BaseAmqp
{
    const NON_PERSISTENT = 1;
    const PERSISTENT = 2;
    
    /**
     * @var AbstractConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var array
     */
    protected $exchangeOptions = array(
        'passive' => false,
        'durable' => true,
        'auto_delete' => false,
        'internal' => false,
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    );

    /**
     * @var array
     */
    protected $queueOptions = array(
        'name' => '',
        'passive' => false,
        'durable' => true,
        'exclusive' => false,
        'auto_delete' => false,
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    );

    /**
     * @var array
     */
    protected $consumerOptions = array(
        'qos' => array()
    );

    /**
     * @var string
     */
    protected $routingKey = '';

    /**
     * @var array
     */
    protected $parameters = array(
        'content_type' => 'text/plain'
    );

    /**
     * BaseAmqp constructor.
     * @param AbstractConnection $connection
     */
    public function __construct(AbstractConnection $connection)
    {
        $this->connection = $connection;
        $this->channel = $this->connection->channel();
    }

    /**
     * @param array $options
     */
    public function setExchangeOptions(array $options)
    {
        if (!isset($options['name']) || !$this->isValidExchangeName($options['name'])) {
            throw new InvalidArgumentException(
                'You must provide an exchange name'
            );
        }

        if (empty($options['type'])) {
            throw new InvalidArgumentException(
                'You must provide an exchange type'
            );
        }

        $this->exchangeOptions = array_merge(
            $this->exchangeOptions,
            $options
        );
    }

    /**
     * @param array $options
     */
    public function setQueueOptions(array $options)
    {
        $this->queueOptions = array_merge(
            $this->queueOptions,
            $options
        );
    }

    /**
     * @param string $routingKey
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
    }

    /**
     * @param array $options
     */
    public function setQos(array $options)
    {
        $this->consumerOptions['qos'] = array_merge($this->consumerOptions['qos'], $options);
    }

    /**
     * Setup consumer.
     */
    protected function setUpConsumer()
    {
        if (isset($this->exchangeOptions['name'])) {
            $this->channel
                ->exchange_declare(
                    $this->exchangeOptions['name'],
                    $this->exchangeOptions['type'],
                    $this->exchangeOptions['passive'],
                    $this->exchangeOptions['durable'],
                    $this->exchangeOptions['auto_delete'],
                    $this->exchangeOptions['internal'],
                    $this->exchangeOptions['nowait'],
                    $this->exchangeOptions['arguments'],
                    $this->exchangeOptions['ticket']
                );

            if (!empty($this->consumerOptions['qos'])) {
                $this->channel
                    ->basic_qos(
                        $this->consumerOptions['qos']['prefetch_size'],
                        $this->consumerOptions['qos']['prefetch_count'],
                        $this->consumerOptions['qos']['global']
                    );
            }
        }

        list($queueName, , ) = $this->channel
            ->queue_declare(
                $this->queueOptions['name'],
                $this->queueOptions['passive'],
                $this->queueOptions['durable'],
                $this->queueOptions['exclusive'],
                $this->queueOptions['auto_delete'],
                $this->queueOptions['nowait'],
                $this->queueOptions['arguments'],
                $this->queueOptions['ticket']
            );

        if (isset($this->exchangeOptions['name'])) {
            $this->channel
                ->queue_bind($queueName, $this->exchangeOptions['name'], $this->routingKey);
        }

        $this->channel
            ->basic_consume(
                $queueName,
                $this->getConsumerTag(),
                false,
                false,
                false,
                false,
                array($this, 'processMessage')
            );
    }

    /**
     * @return string
     */
    protected function getConsumerTag()
    {
        return 'PHPPROCESS_' . getmypid();
    }

    /**
     * Verifies exchange name meets the 0.9.1 protocol standard.
     *
     * letters, digits, hyphen, underscore, period, or colon
     *
     * @param string $exchangeName
     * @return bool
     */
    private function isValidExchangeName($exchangeName)
    {
        return preg_match('/^[A-Za-z0-9_\-\.\;]*$/', $exchangeName);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}

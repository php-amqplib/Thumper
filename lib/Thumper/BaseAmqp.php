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

use \PhpAmqpLib\Connection\AMQPConnection;
use \InvalidArgumentException;

/**
 *
 *
 *
 * @category   Thumper
 * @package    Thumper
 */
class BaseAmqp
{
    protected $conn;
    protected $ch;

    protected $exchangeOptions = array(
        'passive' => false,
        'durable' => true,
        'auto_delete' => false,
        'internal' => false,
        'nowait' => false,
        'arguments' => null,
        'ticket' => null
    );

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

    protected $consumerOptions = array(
        'qos' => array()
    );

    protected $routingKey = '';

    public function __construct(AMQPConnection $conn)
    {
        $this->conn = $conn;
        $this->ch = $this->conn->channel();
    }

    public function setExchangeOptions($options)
    {
        if (empty($options['name'])) {
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

    public function setQueueOptions($options)
    {
        $this->queueOptions = array_merge(
            $this->queueOptions,
            $options
        );
    }

    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
    }

    public function setQos($options)
    {
        $this->consumerOptions['qos'] = array_merge($this->consumerOptions['qos'], $options);
    }

    protected function setUpConsumer()
    {
        $this->ch->exchange_declare(
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
            $this->ch->basic_qos(
                $this->consumerOptions['qos']['prefetch_size'],
                $this->consumerOptions['qos']['prefetch_count'],
                $this->consumerOptions['qos']['global']);
        }

        list($queueName,,) = $this->ch->queue_declare(
            $this->queueOptions['name'],
            $this->queueOptions['passive'],
            $this->queueOptions['durable'],
            $this->queueOptions['exclusive'],
            $this->queueOptions['auto_delete'],
            $this->queueOptions['nowait'],
            $this->queueOptions['arguments'],
            $this->queueOptions['ticket']
        );

        $this->ch->queue_bind(
            $queueName,
            $this->exchangeOptions['name'],
            $this->routingKey
        );
        $this->ch->basic_consume(
            $queueName,
            $this->getConsumerTag(),
            false,
            false,
            false,
            false,
            array($this, 'processMessage')
        );
    }

    protected function getConsumerTag()
    {
        return 'PHPPROCESS_' . getmypid();
    }
}

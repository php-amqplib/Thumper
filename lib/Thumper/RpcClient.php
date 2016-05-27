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

use PhpAmqpLib\Message\AMQPMessage;

class RpcClient extends BaseAmqp
{
    /**
     * @var int
     */
    protected $requests;

    /**
     * @var string[]
     */
    protected $replies;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var int
     */
    protected $requestTimeout = null;

    /**
     * Initialize client.
     */
    public function initClient()
    {
        list($this->queueName, , ) = $this->channel->queue_declare('', false, false, true, true);
        $this->requests = 0;
        $this->replies = array();
    }

    /**
     * Add request to be sent to RPC Server.
     *
     * @param string $messageBody
     * @param string $server
     * @param string $requestId
     * @param string $routingKey
     */
    public function addRequest($messageBody, $server, $requestId, $routingKey = '')
    {
        if (empty($requestId)) {
            throw new \InvalidArgumentException("You must provide a request ID");
        }
        $this->setParameter('correlation_id', $requestId);
        $this->setParameter('reply_to', $this->queueName);

        $message = new AMQPMessage(
            $messageBody,
            $this->getParameters()
        );

        $this->channel
            ->basic_publish($message, $server . '-exchange', $routingKey);

        $this->requests++;
    }

    /**
     * Get replies.
     *
     * @return array
     */
    public function getReplies()
    {
        $this->channel
            ->basic_consume(
                $this->queueName,
                $this->queueName,
                false,
                true,
                false,
                false,
                array($this, 'processMessage')
            );

        while (count($this->replies) < $this->requests) {
            $this->channel
                ->wait(null, false, $this->requestTimeout);
        }

        $this->channel
            ->basic_cancel($this->queueName);

        return $this->replies;
    }

    /**
     * @param AMQPMessage $message
     */
    public function processMessage(AMQPMessage $message)
    {
        $this->replies[$message->get('correlation_id')] = $message->body;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->requestTimeout = $timeout;
    }
}

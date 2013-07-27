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

use \PhpAmqpLib\Message\AMQPMessage;
use \Thumper\BaseAmqp;
use \InvalidArgumentException;

/**
 *
 *
 *
 * @category   Thumper
 * @package    Thumper
 */
class RpcClient extends BaseAmqp
{
    protected $requests = 0;
    protected $replies = array();
    protected $queueName;

    public function initClient()
    {
        list($this->queueName, , ) = $this->ch->queue_declare(
            '',
            false,
            false,
            true,
            true
        );
    }

    public function addRequest(
        $msgBody,
        $server,
        $requestId = null,
        $routingKey = ''
    ) {
        if (empty($requestId)) {
            throw new InvalidArgumentException("You must provide a $requestId");
        }

        $msg = new AMQPMessage(
            $msgBody,
            array(
                'content_type' => 'text/plain',
                'reply_to' => $this->queueName,
                'correlation_id' => $requestId
            )
        );

        $this->ch->basic_publish($msg, $server . '-exchange', $routingKey);

        $this->requests++;
    }

    public function getReplies()
    {
        $this->ch->basic_consume(
            $this->queueName,
            $this->queueName,
            false,
            true,
            false,
            false,
            array($this, 'processMessage')
        );

        while (count($this->replies) < $this->requests) {
            $this->ch->wait();
        }

        $this->ch->basic_cancel($this->queueName);
        return $this->replies;
    }

    public function processMessage($msg)
    {
        $this->replies[$msg->get('correlation_id')] = $msg->body;
    }
}

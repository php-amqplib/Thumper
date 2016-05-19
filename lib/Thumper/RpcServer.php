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

use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;

class RpcServer extends BaseConsumer
{
    /**
     * Initialize Server.
     *
     * @param string $name Server name
     */
    public function initServer($name)
    {
        $this->setExchangeOptions(
            array('name' => $name . '-exchange', 'type' => 'direct')
        );
        $this->setQueueOptions(array('name' => $name . '-queue'));
    }

    /**
     * Start server.
     */
    public function start()
    {
        $this->setUpConsumer();

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * Process message.
     *
     * @param AMQPMessage $message
     * @throws \OutOfBoundsException
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    public function processMessage(AMQPMessage $message)
    {
        try {
            $message->delivery_info['channel']
                ->basic_ack($message->delivery_info['delivery_tag']);
            $result = call_user_func($this->callback, $message->body);
            $this->sendReply($result, $message->get('reply_to'), $message->get('correlation_id'));
        } catch (AMQPRuntimeException $exception) {
            $this->sendReply(
                'error: ' . $exception->getMessage(),
                $message->get('reply_to'),
                $message->get('correlation_id')
            );
        } catch (AMQPInvalidArgumentException $exception) {
            $this->sendReply(
                'error: ' . $exception->getMessage(),
                $message->get('reply_to'),
                $message->get('correlation_id')
            );
        }
    }

    /**
     * Send reply.
     *
     * @param string $result
     * @param string $client
     * @param string $correlationId
     * @throws \PhpAmqpLib\Exception\AMQPInvalidArgumentException
     */
    protected function sendReply($result, $client, $correlationId)
    {
        $this->setParameter('correlation_id', $correlationId);
        $reply = new AMQPMessage(
            $result,
            $this->getParameters()
        );
        $this->channel
            ->basic_publish($reply, '', $client);
    }
}

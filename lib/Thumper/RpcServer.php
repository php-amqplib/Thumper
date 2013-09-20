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
use \Thumper\BaseConsumer;
use \Exception;

/**
 *
 *
 *
 * @category   Thumper
 * @package    Thumper
 */
class RpcServer extends BaseConsumer
{
    public function initServer($name)
    {
        $this->setExchangeOptions(
            array('name' => $name . '-exchange', 'type' => 'direct')
        );
        $this->setQueueOptions(array('name' => $name . '-queue'));
    }

    public function start()
    {
        $this->setUpConsumer();

        while (count($this->ch->callbacks)) {
            $this->ch->wait();
        }
    }

    public function processMessage($msg)
    {
        try {
            $msg->delivery_info['channel']->basic_ack(
                $msg->delivery_info['delivery_tag']
            );
            $result = call_user_func($this->callback, $msg->body);
            $this->sendReply(
                $result,
                $msg->get('reply_to'),
                $msg->get('correlation_id')
            );
        } catch (Exception $e) {
            $this->sendReply(
                'error: ' . $e->getMessage(),
                $msg->get('reply_to'),
                $msg->get('correlation_id')
            );
        }
    }

    protected function sendReply($result, $client, $correlationId)
    {
        $reply = new AMQPMessage(
            $result,
            array(
                'content_type' => 'text/plain',
                'correlation_id' => $correlationId
            )
        );
        $this->ch->basic_publish($reply, '', $client);
    }
}

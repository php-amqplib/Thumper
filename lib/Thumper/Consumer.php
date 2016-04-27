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

class Consumer extends BaseConsumer
{
    /**
     * Number of messages consumed.
     * 
     * @var int
     */
    public $consumed = 0;

    /**
     * Target number of messages to consume.
     * 
     * @var int
     */
    private $target;

    /**
     * @param int $numOfMessages
     */
    public function consume($numOfMessages)
    {
        $this->target = $numOfMessages;

        $this->setUpConsumer();

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * @param AMQPMessage $message
     */
    public function processMessage(AMQPMessage $message)
    {
        call_user_func($this->callback, $message->body);
        $message->delivery_info['channel']
            ->basic_ack($message->delivery_info['delivery_tag']);
        $this->consumed++;
        $this->maybeStopConsumer($message);
    }

    /**
     * @param AMQPMessage $message
     */
    protected function maybeStopConsumer(AMQPMessage $message)
    {
        if ($this->consumed == $this->target) {
            $message->delivery_info['channel']
                ->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }
}

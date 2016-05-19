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

class Producer extends BaseAmqp
{
    /**
     * @var bool
     */
    protected $exchangeReady = false;

    /**
     * @param string $messageBody
     * @param string $routingKey
     */
    public function publish($messageBody, $routingKey = '')
    {
        if (!$this->exchangeReady) {
            if (isset($this->exchangeOptions['name'])) {
                //declare a durable non autodelete exchange
                $this->channel->exchange_declare(
                    $this->exchangeOptions['name'],
                    $this->exchangeOptions['type'],
                    false,
                    true,
                    false
                );
            }
            $this->exchangeReady = true;
        }
        
        $this->setParameter('delivery_mode', self::PERSISTENT);

        $message = new AMQPMessage(
            $messageBody,
            $this->getParameters()
        );
        $this->channel->basic_publish(
            $message,
            $this->exchangeOptions['name'],
            !empty($routingKey) ? $routingKey : $this->routingKey
        );
    }
}

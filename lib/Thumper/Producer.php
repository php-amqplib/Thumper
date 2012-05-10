<?php

require_once(__DIR__ . '/BaseAmqp.php');

use PhpAmqpLib\Message\AMQPMessage;

class Producer extends BaseAmqp
{
    protected $exchangeReady = false;


    public function __construct($host, $port, $user, $pass, $vhost)
    {
        parent::__construct($host, $port, $user, $pass, $vhost);
    }

    public function publish($msgBody, $routingKey = '')
    {
        if (!$this->exchangeReady) {
            //declare a durable non autodelete exchange
            $this->ch->exchange_declare($this->exchangeOptions['name'], $this->exchangeOptions['type'], false, true, false);
            $this->exchangeReady = true;
        }

        $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        $this->ch->basic_publish($msg, $this->exchangeOptions['name'], $routingKey);
    }
}

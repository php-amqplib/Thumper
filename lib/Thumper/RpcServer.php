<?php
namespace Thumper;
require_once(__DIR__ . '/BaseConsumer.php');
use PhpAmqpLib\Message\AMQPMessage,
    Thumper\BaseConsumer,
    Exception;

class RpcServer extends BaseConsumer
{
  public function initServer($name)
  {
    $this->setExchangeOptions(array('name' => $name . '-exchange', 'type' => 'direct'));
    $this->setQueueOptions(array('name' => $name . '-queue'));
  }

  public function start()
  {
    $this->setUpConsumer();

    while(count($this->ch->callbacks))
    {
      $this->ch->wait();
    }
  }

  public function processMessage($msg)
  {
    try
    {
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      $result = call_user_func($this->callback, $msg->body);
      $this->sendReply($result, $msg->get('reply_to'), $msg->get('correlation_id'));
    }
    catch (Exception $e)
    {
      $this->sendReply('error: ' .  $e->getMessage(), $msg->get('reply_to'));
    }
  }

  protected function sendReply($result, $client, $correlationId)
  {
    $reply = new AMQPMessage($result, array('content_type' => 'text/plain', 'correlation_id' => $correlationId));
    $this->ch->basic_publish($reply, '', $client);
  }
}
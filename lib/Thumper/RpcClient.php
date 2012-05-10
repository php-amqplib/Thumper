<?php
namespace Thumper;
require_once(__DIR__ . '/BaseAmqp.php');
use PhpAmqpLib\Message\AMQPMessage,
    Thumper\BaseAmqp,
    InvalidArgumentException;

class RpcClient extends BaseAmqp
{
  protected $requests = 0;
  protected $replies = array();
  protected $queueName;

  public function initClient()
  {
    list($this->queueName,,) = $this->ch->queue_declare("", false, false, true, true);
  }

  public function addRequest($msgBody, $server, $requestId = null, $routingKey = '')
  {
    if(empty($requestId))
    {
      throw new InvalidArgumentException('You must provide a $requestId');
    }

    $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain',
                                           'reply_to' => $this->queueName,
                                           'correlation_id' => $requestId));

    $this->ch->basic_publish($msg, $server . '-exchange', $routingKey);

    $this->requests++;
  }

  public function getReplies()
  {
    $this->ch->basic_consume($this->queueName, $this->queueName, false, true, false, false, array($this, 'processMessage'));

    while(count($this->replies) < $this->requests)
    {
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
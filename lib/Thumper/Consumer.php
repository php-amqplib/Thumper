<?php

require_once(__DIR__ . '/BaseConsumer.php');

class Consumer extends BaseConsumer
{
  var $consumed = 0;

  public function consume($msgAmount)
  {
    $this->target = $msgAmount;
    
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
      call_user_func($this->callback, $msg->body);
      $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
      $this->consumed++;
      $this->maybeStopConsumer($msg);
    }
    catch (Exception $e)
    {
      throw $e;
    }
  }
  
  protected function maybeStopConsumer($msg)
  {
    if($this->consumed == $this->target)
    {
      $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
    }
  }
}

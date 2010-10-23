<?php

require_once(__DIR__ . '/BaseAmqp.php');

class BaseConsumer extends BaseAmqp
{
  protected $callback;
  
  public function setCallback($callback)
  {
    $this->callback = $callback;
  }
}

?>
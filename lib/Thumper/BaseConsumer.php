<?php
namespace Thumper;
use Thumper\BaseAmqp;

class BaseConsumer extends BaseAmqp
{
  protected $callback;

  public function setCallback($callback)
  {
    $this->callback = $callback;
  }
}


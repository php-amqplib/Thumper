<?php
namespace Thumper;
require_once(__DIR__ . '/BaseAmqp.php');
use Thumper\BaseAmqp;
class BaseConsumer extends BaseAmqp
{
  protected $callback;

  public function setCallback($callback)
  {
    $this->callback = $callback;
  }
}

?>
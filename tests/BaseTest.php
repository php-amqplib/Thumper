<?php

namespace Thumper\Test;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $methods
     * @return \PhpAmqpLib\Connection\AbstractConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockConnection($methods = null)
    {
        return $this->getMockBuilder('\PhpAmqpLib\Connection\AbstractConnection')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }
}

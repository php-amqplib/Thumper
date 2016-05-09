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
        $mockConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AbstractConnection')
            ->disableOriginalConstructor();

        if (is_array($methods)) {
            $mockConnection->setMethods($methods);
        }
        return $mockConnection->getMock();
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockChannel()
    {
        return $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param mixed $object
     * @return \ReflectionClass
     */
    protected function getReflection($object)
    {
        static $reflection;

        if ($reflection !== null) {
            return $reflection;
        }

        $reflection = new \ReflectionClass($object);
        return $reflection;
    }

    /**
     * @param mixed $object
     * @param string $name
     * @param mixed $value
     */
    public function setReflectionProperty($object, $name, $value)
    {
        $requestsProperty = $this->getReflectionProperty($object, $name);
        $requestsProperty->setValue($object, $value);
    }

    /**
     * @param mixed $object
     * @param $name
     * @return \ReflectionProperty
     */
    public function getReflectionProperty($object, $name)
    {
        $reflectionClass = $this->getReflection($object);
        $requestsProperty = $reflectionClass->getProperty($name);
        $requestsProperty->setAccessible(true);
        return $requestsProperty;
    }

    /**
     * @param mixed $object
     * @param string $propertyName
     * @return mixed
     */
    public function getReflectionPropertyValue($object, $propertyName)
    {
        $reflectionProperty = $this->getReflectionProperty($object, $propertyName);
        return $reflectionProperty->getValue($object);
    }
}

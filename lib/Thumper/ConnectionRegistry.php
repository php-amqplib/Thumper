<?php

namespace Thumper;

class ConnectionRegistry
{
    protected $connections;

    protected $defaultConnection;

    public function __construct(array $connections, $defaultConnection)
    {
        $this->connections = $connections;
        $this->defaultConnection = $defaultConnection;
    }

    public function getConnection($name = null)
    {
        if (null === $name) {
            $name = $this->defaultConnection;
        }

        if (!isset($this->connections[$name])) {
            throw new \InvalidArgumentException(sprintf('AMQP Connection named "%s" does not exist.', $name));
        }

        return $this->connections[$name];
    }

    public function addConnection($name, $connection)
    {
        $this->connections[$name] = $connection;
    }
}
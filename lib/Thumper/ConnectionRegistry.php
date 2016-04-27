<?php

namespace Thumper;

use PhpAmqpLib\Connection\AbstractConnection;

class ConnectionRegistry
{
    /**
     * @var AbstractConnection[]
     */
    protected $connections;

    /**
     * @var string
     */
    protected $defaultConnection;

    /**
     * ConnectionRegistry constructor.
     *
     * @param AbstractConnection[] $connections Array of connections.
     * @param string $defaultConnection Key of default connection.
     */
    public function __construct(array $connections, $defaultConnection)
    {
        $this->connections = $connections;
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * Return a connection.
     *
     * @param string $name Key of connection to get.
     * @return AbstractConnection
     */
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

    /**
     * Add connection to connections registry.
     *
     * @param string $name Key for connection.
     * @param AbstractConnection $connection Connection object.
     */
    public function addConnection($name, AbstractConnection $connection)
    {
        $this->connections[$name] = $connection;
    }
}

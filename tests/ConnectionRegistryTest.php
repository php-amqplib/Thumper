<?php

namespace Thumper\Test;

use Thumper\ConnectionRegistry;

class ConnectionRegistryTest extends BaseTest
{
    public function testGetConnectionProvidedThroughConstructor()
    {
        $connections = array(
            'default' => $this->getMockConnection()
        );

        $registry = new ConnectionRegistry($connections, 'default');

        $this->assertSame($connections['default'], $registry->getConnection('default'));
        $this->assertSame($connections['default'], $registry->getConnection());
    }

    public function testGetConnectionProvidedAddConnection()
    {
        $connections = array(
            'default' => $this->getMockConnection()
        );

        $registry = new ConnectionRegistry($connections, 'default');

        $newConnection = $this->getMockConnection();

        $registry->addConnection('new-connection', $newConnection);

        $this->assertSame($newConnection, $registry->getConnection('new-connection'));
    }

    public function testGetUnknownConnectionThrowsException()
    {
        $connections = array(
            'default' => $this->getMockConnection()
        );

        $registry = new ConnectionRegistry($connections, 'default');

        $this->setExpectedException('\InvalidArgumentException', 'AMQP Connection named "not-found" does not exist.');
        $registry->getConnection('not-found');
    }
}

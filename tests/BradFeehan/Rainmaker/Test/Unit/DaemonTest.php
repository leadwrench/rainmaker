<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use BradFeehan\Rainmaker\Daemon;
use BradFeehan\Rainmaker\Test\UnitTestCase;
use ReflectionClass;
use ReflectionMethod;

class DaemonTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Daemon::__construct
     */
    public function testConstruct()
    {
        $daemon = new Daemon(
            \Mockery::mock('BradFeehan\\Rainmaker\\Configuration'),
            \Mockery::mock('BradFeehan\\Rainmaker\\ExecuterInterface')
        );

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Daemon',
            $daemon
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::start
     */
    public function testStart()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('debug')
                ->with('Daemon::start()')
            ->getMock();

        $executer = \Mockery::mock('BradFeehan\\Rainmaker\\ExecuterInterface')
            ->shouldReceive('start')
                ->withNoArgs()
            ->getMock();

        $daemon = $this->daemon('start')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('getExecuter')
                ->withNoArgs()
                ->andReturn($executer)
            ->getMock();

        $daemon->start();

        // To prevent PHPUnit complaining about no assertions -- the
        // real assertions are done by Mockery
        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::getConfiguration
     */
    public function testGetConfiguration()
    {
        $configuration = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Configuration'
        );

        $daemon = new Daemon($configuration);

        $this->assertSame(
            $configuration,
            $daemon->getConfiguration()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::getExecuter
     */
    public function testGetExecuter()
    {
        $configuration = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Configuration'
        );

        $executer = \Mockery::mock(
            'BradFeehan\\Rainmaker\\ExecuterInterface'
        );

        $daemon = new Daemon($configuration, $executer);

        $this->assertSame(
            $executer,
            $daemon->getExecuter()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::getExecuter
     */
    public function testGetExecuterDefaultValue()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface');

        $configuration = \Mockery::mock('BradFeehan\\Rainmaker\\Configuration')
            ->shouldReceive('get')
                ->with('interval')
                ->andReturn(10)
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->getMock();

        $daemon = $this->daemon('getExecuter')
            ->shouldReceive('getConfiguration')
                ->withNoArgs()
                ->andReturn($configuration)
            ->getMock();

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\ExecuterInterface',
            $daemon->getExecuter()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::logger
     */
    public function testLogger()
    {
        $configuration = \Mockery::mock('BradFeehan\\Rainmaker\\Configuration')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn('$logger')
            ->getMock();

        $daemon = $this->daemon('logger', 'configuration')
            ->shouldReceive('getConfiguration')
                ->withNoArgs()
                ->andReturn($configuration)
            ->getMock();

        $this->assertSame(
            '$logger',
            $daemon->logger()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::tick
     */
    public function testTick()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('debug')
                ->with('Daemon::tick()')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Check starting')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Checking mailbox \'$mailbox1\'...')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Found $count1a messages in total')
                ->once()->ordered()
            ->shouldReceive('notice')
                ->with('Found $count1b feedback reports')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Checking mailbox \'$mailbox2\'...')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Found $count2a messages in total')
                ->once()->ordered()
            ->shouldReceive('notice')
                ->with('Found $count2b feedback reports')
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Check successfully completed')
                ->once()->ordered()
            ->getMock();

        $message1 = \Mockery::mock();
        $message2 = \Mockery::mock();

        $innerIterator1 = \Mockery::mock()
            ->shouldReceive('countMessages')
                ->withNoArgs()
                ->andReturn('$count1a')
            ->getMock();

        $mailbox1 = \Mockery::mock(
            'ArrayIterator[getName,refresh,getInnerIterator,count]',
            array(array($message1))
        )
            ->shouldReceive('getName')
                ->withNoArgs()
                ->andReturn('$mailbox1')
            ->shouldReceive('refresh')
                ->withNoArgs()
            ->shouldReceive('getInnerIterator')
                ->withNoArgs()
                ->andReturn($innerIterator1)
            ->shouldReceive('count')
                ->withNoArgs()
                ->andReturn('$count1b')
            ->getMock();

        $innerIterator2 = \Mockery::mock()
            ->shouldReceive('countMessages')
                ->withNoArgs()
                ->andReturn('$count2a')
            ->getMock();

        $mailbox2 = \Mockery::mock(
            'ArrayIterator[getName,refresh,getInnerIterator,count]',
            array(array($message2))
        )
            ->shouldReceive('getName')
                ->withNoArgs()
                ->andReturn('$mailbox2')
            ->shouldReceive('refresh')
                ->withNoArgs()
            ->shouldReceive('getInnerIterator')
                ->withNoArgs()
                ->andReturn($innerIterator2)
            ->shouldReceive('count')
                ->withNoArgs()
                ->andReturn('$count2b')
            ->getMock();

        $mailboxes = array($mailbox1, $mailbox2);


        $configuration = \Mockery::mock('BradFeehan\\Rainmaker\\Configuration')
            ->shouldReceive('getMailboxes')
                ->withNoArgs()
                ->andReturn($mailboxes)
            ->getMock();

        $daemon = $this->daemon('tick')
            ->shouldReceive('getConfiguration')
                ->withNoArgs()
                ->andReturn($configuration)
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('processMessage')
                ->with($message1)
                ->once()
            ->shouldReceive('processMessage')
                ->with($message2)
                ->once()
            ->getMock();

        $daemon->tick();

        // To prevent PHPUnit complaining about no assertions -- the
        // real assertions are done by Mockery
        $this->assertTrue(true);
    }


    /**
     * Creates a partially-mocked Daemon object
     *
     * Allows specifying which methods should NOT be mocked
     *
     * @param string $methodNameX A method that shouldn't be mocked
     *
     * @return BradFeehan\Rainmaker\Daemon
     */
    private function daemon(/* $methodName1, $methodName2, ... */)
    {
        $unmockedMethodNames = func_get_args();

        // Determine which methods should be mocked
        $mockedMethodNames = array();
        $reflectionClass = new ReflectionClass('BradFeehan\\Rainmaker\\Daemon');

        $allMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($allMethods as $method) {
            $methodName = $method->getName();

            // Mock any methods that aren't in the unmocked method list
            if (!in_array($methodName, $unmockedMethodNames)) {
                $mockedMethodNames[] = $methodName;
            }
        }

        // Create the class as a partial mock
        $methods = implode(',', $mockedMethodNames);
        return \Mockery::mock(
            "BradFeehan\\Rainmaker\\Daemon[{$methods}]",
            array(
                \Mockery::mock('BradFeehan\\Rainmaker\\Configuration')
            )
        );
    }
}

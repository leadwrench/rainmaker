<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use BradFeehan\Rainmaker\Configuration;
use BradFeehan\Rainmaker\Test\UnitTestCase;
use ReflectionClass;
use ReflectionMethod;

class ConfigurationTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Configuration::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Configuration',
            new Configuration()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::process
     */
    public function testProcess()
    {
        $processor = \Mockery::mock(
            'Symfony\\Component\\Config\\Definition\\Processor'
        );

        $configuration = new Configuration($processor);

        $processor
            ->shouldReceive('processConfiguration')
                ->with($configuration, array('$foo', '$bar'))
                ->andReturn('$result');

        // Assert chainability
        $this->assertSame(
            $configuration,
            $configuration->process('$foo', '$bar')
        );

        // Check that the result is stored
        $this->assertSame('$result', $configuration->get());
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::getConfigTreeBuilder
     * @covers BradFeehan\Rainmaker\Configuration::process
     * @dataProvider dataProcessInvalid
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testProcessInvalid($data = array())
    {
        $configuration = new Configuration();
        $configuration->process($data);
    }

    public function dataProcessInvalid()
    {
        return array(
            array(
                array(
                    'mailboxes' => array(
                        array(
                            'protocol' => 'imap',
                            'host' => '$host',
                        ),
                    ),
                    'logger' => array('class' => 'foo'),
                ),
            ),
            array(
                array(
                    'mailboxes' => array(
                        array(
                            'protocol' => 'pop',
                            'host' => '$host',
                        ),
                    ),
                    'logger' => array(
                        'class' => get_class(
                            \Mockery::mock('Psr\\Log\\LoggerInterface')
                        ),
                        'configuration' => array(
                            'class' => 'foo',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::get
     */
    public function testGetWithNoArguments()
    {
        $data = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );

        $this->assertSame($data, $this->configuration($data)->get());
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::get
     */
    public function testGetWithKey()
    {
        $data = array(
            'bar' => 'baz',
            'qux' => 'foo',
        );

        $configuration = $this->configuration($data);
        $this->assertSame('baz', $configuration->get('bar'));
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::get
     * @expectedException BradFeehan\Rainmaker\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown configuration key 'lol'
     */
    public function testGetWithInvalidKey()
    {
        $data = array(
            'bar' => 'baz',
            'qux' => 'foo',
        );

        $configuration = $this->configuration($data);
        $configuration->get('lol');
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::get
     */
    public function testGetWithDeepKey()
    {
        $data = array(
            'foo' => array('bar' => '$result'),
        );

        $configuration = $this->configuration($data);
        $this->assertSame('$result', $configuration->get('foo/bar'));
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::logger()
     * @covers BradFeehan\Rainmaker\Configuration::createLogger()
     */
    public function testLogger()
    {
        $logger = \Mockery::mock();
        $configurer = \Mockery::mock('overload:MockConfigurer')
            ->shouldReceive('createLogger')
                ->with(get_class($logger))
                ->andReturn($logger)
            ->getMock();

        $configuration = $this->configuration(array(
            'logger' => array(
                'class' => get_class($logger),
                'configuration' => array(
                    'class' => get_class($configurer),
                ),
            ),
        ));

        $this->assertInstanceOf(get_class($logger), $configuration->logger());
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::getMailboxes
     * @covers BradFeehan\Rainmaker\Configuration::createMailbox
     */
    public function testGetMailboxes()
    {
        $mailboxConfig1 = array(
            'name' => '$mailbox1',
            'protocol' => 'imap',
        );

        $mailboxConfig2 = array(
            'name' => '$mailbox2',
            'protocol' => 'pop',
        );


        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('debug')
                ->with('Daemon::createMailbox()', $mailboxConfig1)
                ->once()->ordered(10)
            ->shouldReceive('info')
                ->with('Connecting to mailbox \'$mailbox1\'...')
                ->once()->ordered(20)
            ->shouldReceive('debug')
                ->with('Daemon::createMailbox()', $mailboxConfig2)
                ->once()->ordered(30)
            ->shouldReceive('info')
                ->with('Connecting to mailbox \'$mailbox2\'...')
                ->once()->ordered(40)
            ->getMock()
        ;

        $mailbox1 = \Mockery::mock();
        $mailbox2 = \Mockery::mock();

        $configuration = $this->mock('getMailboxes', 'createMailbox')
            ->shouldReceive('get')
                ->with('mailboxes')
                ->andReturn(array($mailboxConfig1, $mailboxConfig2))
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('createObject')
                ->with(
                    'Zend\\Mail\\Storage\\Imap',
                    array(
                        array(
                            'name' => '$mailbox1',
                            'host' => '',
                            'port' => null,
                            'ssl' => false,
                        )
                    )
                )
                ->andReturn($mailbox1)
            ->shouldReceive('createObject')
                ->with(
                    'Zend\\Mail\\Storage\\Pop',
                    array('', null, false)
                )
                ->andReturn($mailbox2)
            ->shouldReceive('createObject')
                ->with(
                    'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox',
                    array($mailbox1, '$mailbox1')
                )
                ->andReturn('$filterMailbox1')
            ->shouldReceive('createObject')
                ->with(
                    'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox',
                    array($mailbox2, '$mailbox2')
                )
                ->andReturn('$filterMailbox2')
            ->getMock()
        ;

        $result = $configuration->getMailboxes();

        $this->assertInternalType('array', $result);
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::getMailboxes
     * @covers BradFeehan\Rainmaker\Configuration::createMailbox
     * @expectedException BradFeehan\Rainmaker\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid mailbox protocol '$invalid_protocol'
     */
    public function testGetMailboxesWithInvalidProtocol()
    {
        $mailboxConfig = array(
            'name' => '$mailbox',
            'protocol' => '$invalid_protocol',
        );

        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('debug')
                ->with('Daemon::createMailbox()', $mailboxConfig)
                ->once()->ordered()
            ->shouldReceive('info')
                ->with('Connecting to mailbox \'$mailbox\'...')
                ->once()->ordered()
            ->getMock()
        ;

        $mailbox = \Mockery::mock();

        $configuration = $this->mock('getMailboxes', 'createMailbox')
            ->shouldReceive('get')
                ->with('mailboxes')
                ->andReturn(array($mailboxConfig))
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->getMock()
        ;

        $result = $configuration->getMailboxes();
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertInstanceOf(
            'Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder',
            $configuration->getConfigTreeBuilder()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Configuration::createObject
     */
    public function testCreateObject()
    {
        $array = array('foo' => '$foo', 'bar' => '$bar');

        $configuration = $this->configuration();
        $result = $configuration->createObject('ArrayObject', array($array));

        $this->assertSame($array, $result->getArrayCopy());
    }


    private function configuration($data = array())
    {
        $processor = \Mockery::mock(
            'Symfony\\Component\\Config\\Definition\\Processor'
        );

        $configuration = new Configuration($processor);

        $processor
            ->shouldReceive('processConfiguration')
                ->with($configuration, array($data))
                ->andReturn($data);

        $configuration->process($data);

        return $configuration;
    }

    /**
     * Creates a partially-mocked Configuration object
     *
     * Allows specifying which methods should NOT be mocked
     *
     * @param string $methodNameX A method that shouldn't be mocked
     *
     * @return BradFeehan\Rainmaker\Configuration
     */
    private function mock(/* $methodName1, $methodName2, ... */)
    {
        $unmockedMethodNames = func_get_args();

        // Determine which methods should be mocked
        $mockedMethodNames = array();
        $reflectionClass = new ReflectionClass(
            'BradFeehan\\Rainmaker\\Configuration'
        );

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
            "BradFeehan\\Rainmaker\\Configuration[{$methods}]"
        );

        return $daemon;
    }
}

<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use BradFeehan\Rainmaker\Configuration;
use BradFeehan\Rainmaker\Test\UnitTestCase;

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
}

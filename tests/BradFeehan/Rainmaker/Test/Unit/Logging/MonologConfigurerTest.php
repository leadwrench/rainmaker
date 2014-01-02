<?php

namespace BradFeehan\Rainmaker\Test\Unit\Logging;

use BradFeehan\Rainmaker\Logging\MonologConfigurer;
use BradFeehan\Rainmaker\Test\UnitTestCase;

class MonologConfigurerTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Logging\MonologConfigurer::createLogger
     */
    public function testCreateLogger()
    {
        $configurer = new MonologConfigurer();

        $this->assertInstanceOf(
            'Monolog\\Logger',
            $configurer->createLogger('Monolog\\Logger')
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Logging\MonologConfigurer::createLogger
     * @expectedException BradFeehan\Rainmaker\Exception\InvalidArgumentException
     * @expectedExceptionMessage MonologConfigurer can only create a Monolog logger
     */
    public function testCreateLoggerWithInvalidClassName()
    {
        $configurer = new MonologConfigurer();
        $configurer->createLogger('stdClass');
    }

    /**
     * @covers BradFeehan\Rainmaker\Logging\MonologConfigurer::configure
     */
    public function testConfigure()
    {
        $logger = \Mockery::mock('Monolog\\Logger')
            ->shouldReceive('pushHandler')
                ->with(\Mockery::type('Monolog\\Handler\\HandlerInterface'))
            ->getMock();

        $configurer = new MonologConfigurer();

        $this->assertSame(
            $logger,
            $configurer->configure($logger)
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Logging\MonologConfigurer::getHandler
     */
    public function testGetHandler()
    {
        $configurer = new MonologConfigurer();
        $this->assertInstanceOf(
            'Monolog\\Handler\\HandlerInterface',
            $configurer->getHandler()
        );
    }
}

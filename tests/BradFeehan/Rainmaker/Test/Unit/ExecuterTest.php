<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use BradFeehan\Rainmaker\Executer;
use BradFeehan\Rainmaker\Test\UnitTestCase;

class ExecuterTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Executer::__construct
     */
    public function testConstructValid()
    {
        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Executer',
            $this->executer()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::__construct
     * @dataProvider dataConstructInvalid
     * @expectedException BradFeehan\Rainmaker\Exception\InvalidArgumentException
     */
    public function testConstructInvalid($tickFunction, $sleepDuration)
    {
        $executer = new Executer($tickFunction, $sleepDuration);
    }

    public function dataConstructInvalid()
    {
        return array(
            array($this->tickFunction(), -1),
            array(false, 2),
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::isRunning
     */
    public function testIsRunning()
    {
        $this->assertFalse($this->executer()->isRunning());
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::logger
     */
    public function testLogger()
    {
        $this->assertInstanceOf(
            'Psr\\Log\\LoggerInterface',
            $this->executer()->logger()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::tick
     */
    public function testTick()
    {
        $tickFunction = function () {
            return '$tickReturn';
        };

        $executer = new Executer($tickFunction, 2);

        $this->assertSame(
            '$tickReturn',
            $this->invokePrivateMethod($executer, 'tick')
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::sleep
     */
    public function testSleep()
    {
        $executer = new Executer($this->tickFunction(), 0.001); // 1 ms
        $this->invokePrivateMethod($executer, 'sleep');
        $this->assertTrue(true); // no assertions to perform
    }

    /**
     * @covers BradFeehan\Rainmaker\Executer::signalHandlerCallback
     */
    public function testSignalHandlerCallback()
    {
        $executer = $this->executer();
        $executer->signalHandlerCallback(0);
        $this->assertFalse($executer->isRunning());
    }


    private function executer()
    {
        return new Executer($this->tickFunction(), 2);
    }

    private function tickFunction($returnFunction = null)
    {
        if (!is_callable($returnFunction)) {
            $returnFunction = function () {};
        }

        $tickFunctionObject = \Mockery::mock('stdClass')
            ->shouldReceive('tickFunction')
                ->once()
                ->andReturnUsing($returnFunction)
            ->getMock();

        return array($tickFunctionObject, 'tickFunction');
    }
}

<?php

namespace BradFeehan\Rainmaker\Test\Unit\Logging;

use BradFeehan\Rainmaker\Logging\SanitizationProcessor;
use BradFeehan\Rainmaker\Test\UnitTestCase;

class SanitizationProcessorTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Logging\SanitizationProcessor::__construct
     */
    public function testConstruct()
    {
        $processor = new SanitizationProcessor();

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Logging\\SanitizationProcessor',
            $processor
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Logging\SanitizationProcessor::__invoke
     */
    public function testInvoke()
    {
        $processor = new SanitizationProcessor(100, array('/^foo$/'));

        $record = array(
            'level' => 200,
            'context' => array(
                'foo' => 'bar',
                'foobar' => 'baz',
            ),
        );

        $result = $processor($record);

        $this->assertSame(
            array(
                'level' => 200,
                'context' => array(
                    'foo' => '***',
                    'foobar' => 'baz',
                ),
            ),
            $result
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Logging\SanitizationProcessor::__invoke
     */
    public function testInvokeWithIgnoredLevel()
    {
        $processor = new SanitizationProcessor(100, array('/^foo$/'));

        $record = array(
            'level' => 50,
            'context' => array(
                'foo' => 'bar',
                'foobar' => 'baz',
            ),
        );

        $result = $processor($record);

        // Shouldn't have been modified
        $this->assertSame($record, $result);
    }
}

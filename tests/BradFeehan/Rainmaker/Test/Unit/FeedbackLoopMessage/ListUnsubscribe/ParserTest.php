<?php

namespace BradFeehan\Rainmaker\Test\Unit\FeedbackLoopMessage\ListUnsubscribe;

use BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser;
use BradFeehan\Rainmaker\Test\UnitTestCase;
use Zend\Mail\Header\GenericHeader;

class ParserTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser::__construct
     */
    public function testConstruct()
    {
        $parser = new Parser($this->header());
        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage\\' .
            'ListUnsubscribe\\ParserInterface',
            $parser
        );
    }

    /**
     * @param Zend\Mail\Header\GenericHeader $header   The input header
     * @param array                          $expected Expected output
     *
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser::parse
     * @dataProvider dataParse
     */
    public function testParse(GenericHeader $header, $expected)
    {
        $parser = new Parser($header);
        $this->assertSame($expected, $parser->parse());
    }

    public function dataParse()
    {
        return array(
            // Simple example
            array(
                $this->header('<value1>, <value2>'),
                array('value1', 'value2'),
            ),

            // Examples from RFC 2369
            array(
                $this->header('<mailto:list@host.com?subject=unsubscribe>'),
                array('mailto:list@host.com?subject=unsubscribe'),
            ),
            array(
                $this->header(
                    '(Use this command to get off the list) ' .
                    '<mailto:list-manager@host.com?body=unsubscribe%20list>'
                ),
                array('mailto:list-manager@host.com?body=unsubscribe%20list'),
            ),
            array(
                $this->header('<mailto:list-off@host.com>'),
                array('mailto:list-off@host.com'),
            ),
            array(
                $this->header(
                    '<http://www.host.com/list.cgi?cmd=unsub&lst=list>, ' .
                    '<mailto:list-request@host.com?subject=unsubscribe>'
                ),
                array(
                    'http://www.host.com/list.cgi?cmd=unsub&lst=list',
                    'mailto:list-request@host.com?subject=unsubscribe',
                ),
            ),

            // Invalid examples
            array(
                $this->header('Invalid header with no angle brackets'),
                array(),
            ),
            array(
                $this->header('<Invalid header with no close-bracket'),
                array(),
            ),
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser::parse
     */
    public function testParseStripsWhitespace()
    {
        $header = $this->header(" <  value 1 >, \t< value  2 >  ");
        $parser = new Parser($header);
        $this->assertSame(array('value1', 'value2'), $parser->parse());
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser::parse
     */
    public function testParseWithComplexValue()
    {
        $header = $this->header(" <  value 1 >, \t< value  2 >  ");
        $parser = new Parser($header);
        $this->assertSame(array('value1', 'value2'), $parser->parse());
    }


    /**
     * Creates a mock header object with a particular value
     *
     * @param string The value that the mock header will return
     *               (optional, no method stubs are created if omitted)
     *
     * @return Zend\Mail\Header\GenericHeader
     */
    private function header($value = null)
    {
        $header = \Mockery::mock('Zend\\Mail\\Header\\GenericHeader')
            ->shouldReceive('__toString')
                ->andReturn('Mock header: ' . $this->dump($value))
            ->getMock();

        if ($value) {
            $header->shouldReceive('getFieldValue')->andReturn($value);
        }

        return $header;
    }

    /**
     * Returns the output from var_dump on a variable
     *
     * @param mixed $value The variable to var_dump
     *
     * @return string
     */
    private function dump($value)
    {
        // Turn on output buffering
        ob_start();

        // Fill the output buffer with the output from var_dump($value)
        var_dump($value);

        // Return contents of the output buffer, and erase it
        return ob_get_clean();
    }
}

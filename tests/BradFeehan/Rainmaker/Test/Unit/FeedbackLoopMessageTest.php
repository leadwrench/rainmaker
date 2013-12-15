<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use ArrayIterator;
use BradFeehan\Rainmaker\FeedbackLoopMessage;
use BradFeehan\Rainmaker\Test\UnitTestCase;
use ReflectionObject;

class FeedbackLoopMessageTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage',
            $this->message()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::getParser
     */
    public function testGetParser()
    {
        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\FeedbackLoopMessage\\ListUnsubscribe\\ParserInterface',
            $this->message()->getParser()
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::getUnsubscribeUrls
     */
    public function testGetUnsubscribeUrls()
    {
        $part1 = $this->part();
        $part2 = $this->part();

        $source = new ArrayIterator(array($part1, $part2));
        $source = \Mockery::mock($source)
            ->shouldReceive('isMultipart')
                ->andReturn(true)
            ->getMock();

        $message = \Mockery::mock(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage[parsePart]',
            array($source)
        );

        $message
            ->shouldReceive('parsePart')
                ->with($part1)
                ->andReturn(null)
            ->shouldReceive('parsePart')
                ->with($part2)
                ->andReturn(array('$result'));

        $this->assertSame(array('$result'), $message->getUnsubscribeUrls());
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::getUnsubscribeUrls
     * @dataProvider dataGetUnsubscribeUrlsInvalid
     */
    public function testGetUnsubscribeUrlsInvalid($source)
    {
        $message = \Mockery::mock(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage[parsePart]',
            array($source)
        );

        $message
            ->shouldReceive('parsePart')
                ->andReturn(null);

        $this->assertSame(array(), $message->getUnsubscribeUrls());
    }

    public function dataGetUnsubscribeUrlsInvalid()
    {
        $part = $this->part();
        $source = new ArrayIterator(array($part));

        return array(
            // This source message isn't multipart
            array(
                \Mockery::mock($source)
                    ->shouldReceive('isMultipart')
                        ->andReturn(false)
                    ->getMock(),
            ),

            // This source is multipart but won't have any headers
            array(
                \Mockery::mock($source)
                    ->shouldReceive('isMultipart')
                        ->andReturn(true)
                    ->getMock(),
            ),
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::parsePart
     */
    public function testParsePart()
    {
        $part = $this->part()
            ->shouldReceive('getContent')
                ->andReturn(
                    "From: test@example.com\n" .
                    "To: test2@example.com\n" .
                    "List-Unsubscribe: \$value\n\n" .
                    "Message content"
                )
            ->getMock();

        $parser = $this->parser()
            ->shouldReceive('parse')
                ->andReturn('$result')
            ->getMock();

        $message = $this->message(null, $parser);
        $this->assertSame('$result', $message->parsePart($part));
    }

    /**
     * @param Zend\Mail\Storage\Part\PartInterface $part
     *
     * @covers BradFeehan\Rainmaker\FeedbackLoopMessage::parsePart
     * @dataProvider dataParsePartInvalid
     */
    public function testParsePartInvalid($part)
    {
        $parser = $this->parser();
        $message = $this->message(null, $parser);
        $this->assertNull($message->parsePart($part));
    }

    public function dataParsePartInvalid()
    {
        return array(
            // This part has the wrong content-type for a message, and
            // should be ignored
            array($this->part('application/octet-stream'), null),

            // This one doesn't have the List-Unsubscribe header, so it
            // also should be ignored
            array(
                $this->part()
                    ->shouldReceive('getContent')
                        ->andReturn(
                            "From: test@example.com\n" .
                            "To: test2@example.com\n\n" .
                            "Test"
                        )
                    ->getMock(),
                null,
            ),
        );
    }


    private function message($source = null, $parser = null)
    {
        if (!$source) {
            $source = \Mockery::mock();
        }

        return new FeedbackLoopMessage($source, $parser);
    }

    /**
     * Creates a mock message part
     *
     * @param string $contentType Value for the part's content-type
     *
     * @return Mockery\MockInterface
     */
    private function part($contentType = 'message/rfc822')
    {
        $part = \Mockery::mock('Part');
        $part->contentType = $contentType;
        return $part;
    }

    /**
     * Creates a mock parser object
     *
     * This mock parser usually gets dependency-injected into the
     * FeedbackLoopMessage being tested, and can be used to provide
     * expectations about the actions taken upon it.
     *
     * @return BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface
     */
    private function parser()
    {
        return \Mockery::mock(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage\\' .
            'ListUnsubscribe\\ParserInterface'
        );
    }
}

<?php

namespace BradFeehan\Rainmaker\Test\Unit\Mailbox;

use ArrayIterator;
use BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox;
use BradFeehan\Rainmaker\Test\UnitTestCase;
use stdClass;
use Zend\Mail\Storage\Message;

class FeedbackLoopFilterMailboxTest extends UnitTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::__construct
     */
    public function testConstruct()
    {
        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[]',
            array(\Mockery::mock('Iterator'), '$test') // needs constructor arg
        );

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox',
            $mailbox
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::getName
     */
    public function testGetName()
    {
        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[]',
            array(\Mockery::mock('Iterator'), '$test') // needs constructor arg
        );

        $this->assertSame($mailbox->getName(), '$test');
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAccept()
    {
        $contentType = \Mockery::mock('Zend\\Mail\\Header\\ContentType')
            ->shouldReceive('getType')
                ->andReturn('multipart/report')
            ->shouldReceive('getParameter')
                ->with('report-type')
                ->andReturn('feedback-report')
            ->getMock();

        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andReturn($contentType)
            ->getMock();

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertTrue($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAcceptWithNonMessage()
    {
        $original = \Mockery::mock('NotAZendMessage');

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertFalse($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAcceptWithWrongContentType()
    {
        $contentType = \Mockery::mock('Zend\\Mail\\Header\\ContentType')
            ->shouldReceive('getType')
                ->andReturn('multipart/mixed')
            ->shouldReceive('toString')
                ->andReturn('$contentType')
            ->getMock();

        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andReturn($contentType)
            ->getMock();

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertFalse($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAcceptWithNoContentType()
    {
        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andThrow('Zend\\Mail\\Storage\\Exception\\InvalidArgumentException')
            ->getMock();

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertFalse($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAcceptWithWrongReportType()
    {
        $contentType = \Mockery::mock('Zend\\Mail\\Header\\ContentType')
            ->shouldReceive('getType')
                ->andReturn('multipart/report')
            ->shouldReceive('getParameter')
                ->with('report-type')
                ->andReturn('not-a-feedback-report')
            ->getMock();

        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andReturn($contentType)
            ->getMock();

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertFalse($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::accept
     */
    public function testAcceptWithNoReportType()
    {
        $contentType = \Mockery::mock('Zend\\Mail\\Header\\ContentType')
            ->shouldReceive('getType')
                ->andReturn('multipart/report')
            ->shouldReceive('getParameter')
                ->with('report-type')
                ->andThrow('Zend\\Mail\\Storage\\Exception\\InvalidArgumentException')
            ->shouldReceive('toString')
                ->andReturn('$contentType')
            ->getMock();

        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andReturn($contentType)
            ->getMock();

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $this->assertFalse($mailbox->accept());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::current
     */
    public function testCurrent()
    {
        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message');

        $mailbox = \Mockery::mock(
            'BradFeehan\\Rainmaker\\Mailbox\\FeedbackLoopFilterMailbox[original]',
            array(\Mockery::mock('Iterator'), 'test') // needs constructor arg
        );
        $mailbox->shouldReceive('original')->andReturn($original);

        $current = $mailbox->current();

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\FeedbackLoopMessage',
            $current
        );

        $this->assertSame($original, $current->getSource());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::original
     */
    public function testOriginal()
    {
        $original = \Mockery::mock('Zend\\Mail\\Storage\\Message');

        $innerMailbox = new ArrayIterator(array($original));
        $mailbox = new FeedbackLoopFilterMailbox($innerMailbox, 'test');

        $this->assertSame($original, $mailbox->original());
    }

    /**
     * @covers BradFeehan\Rainmaker\Mailbox\FeedbackLoopFilterMailbox::count
     */
    public function testCount()
    {
        $contentType = \Mockery::mock('Zend\\Mail\\Header\\ContentType')
            ->shouldReceive('getType')
                ->andReturn('multipart/report')
            ->shouldReceive('getParameter')
                ->with('report-type')
                ->andReturn('feedback-report')
            ->getMock();

        $feedbackLoopMessage = \Mockery::mock('Zend\\Mail\\Storage\\Message')
            ->shouldReceive('getHeader')
                ->with('Content-Type')
                ->andReturn($contentType)
            ->getMock();


        $innerMailbox = new ArrayIterator(array(
            $feedbackLoopMessage,
            $feedbackLoopMessage,
            new stdClass(),
            $feedbackLoopMessage,
        ));

        $mailbox = new FeedbackLoopFilterMailbox($innerMailbox, 'test');

        $this->assertSame(3, count($mailbox));
    }
}

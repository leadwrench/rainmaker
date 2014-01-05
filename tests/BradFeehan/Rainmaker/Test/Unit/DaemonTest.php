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
     * @covers BradFeehan\Rainmaker\Daemon::processMessage
     */
    public function testProcessMessage()
    {
        $message = \Mockery::mock('BradFeehan\\Rainmaker\\FeedbackLoopMessage')
            ->shouldReceive('getUnsubscribeUrls')
                ->withNoArgs()
                ->andReturn(array('http://url1', '$url2'))
            ->getMock();

        $message
            ->shouldReceive('getSource->getHeader->toString')
            ->andReturn('$subject');

        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with(
                    'Processing feedback report',
                    array('subject' => '$subject')
                )
                ->once()->ordered()
            ->shouldReceive('notice')
                ->with("Found URLs: 'http://url1', '\$url2'.")
                ->once()->ordered()
            ->getMock();

        $daemon = $this->daemon('processMessage')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('request')
                ->with('http://url1')
                ->once()->ordered()
            ->getMock();

        $daemon->processMessage($message);

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::processMessage
     */
    public function testProcessMessageWithNoSubject()
    {
        $message = \Mockery::mock('BradFeehan\\Rainmaker\\FeedbackLoopMessage')
            ->shouldReceive('getUnsubscribeUrls')
                ->withNoArgs()
                ->andReturn(array('$url1', '$url2'))
            ->getMock();

        $message
            ->shouldReceive('getSource->getHeader')
            ->andThrow(
                'Zend\\Mail\\Storage\\Exception\\InvalidArgumentException'
            );

        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with(
                    'Processing feedback report',
                    array('subject' => '(No subject)')
                )
                ->once()->ordered()
            ->shouldReceive('notice')
                ->with("Found URLs: '\$url1', '\$url2'.")
                ->once()->ordered()
            ->getMock();

        $daemon = $this->daemon('processMessage')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('request')
                ->with('$url1')
                ->once()->ordered()
            ->shouldReceive('request')
                ->with('$url2')
                ->once()->ordered()
            ->getMock();

        $daemon->processMessage($message);

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::request
     */
    public function testRequest()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with('Sending HTTP request', array('url' => '$url'))
                ->once()->ordered()
            ->getMock();

        $request = \Mockery::mock('Guzzle\\Http\\Message\\RequestInterface')
            ->shouldReceive('send')
                ->withNoArgs()
                ->once()->ordered()
            ->getMock();

        $guzzleClient = \Mockery::mock('Guzzle\\Http\\ClientInterface')
            ->shouldReceive('get')
                ->with('$url')
                ->andReturn($request)
            ->getMock();

        $daemon = $this->daemon('request')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('guzzleClient')
                ->withNoArgs()
                ->andReturn($guzzleClient)
            ->getMock();


        $daemon->request('$url');

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::request
     */
    public function testRequestWithHTTP404NotFoundError()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with('Sending HTTP request', array('url' => '$url'))
                ->once()->ordered()
            ->shouldReceive('warning')
                ->with(
                    'Unsubscribe URL returned HTTP 404 Not Found',
                    array('url' => '$url')
                )
                ->once()->ordered()
            ->getMock();

        $exception = \Mockery::mock(
            'Guzzle\\Http\\Exception\\ClientErrorResponseException'
        );

        $exception
            ->shouldReceive('getResponse->getStatusCode')
                ->withNoArgs()
                ->andReturn(404);

        $request = \Mockery::mock('Guzzle\\Http\\Message\\RequestInterface')
            ->shouldReceive('send')
                ->withNoArgs()
                ->andThrow($exception)
            ->getMock();

        $guzzleClient = \Mockery::mock('Guzzle\\Http\\ClientInterface')
            ->shouldReceive('get')
                ->with('$url')
                ->andReturn($request)
            ->getMock();

        $daemon = $this->daemon('request')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('guzzleClient')
                ->withNoArgs()
                ->andReturn($guzzleClient)
            ->getMock();

        $daemon->request('$url');

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::request
     */
    public function testRequestWithOtherClientError()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with('Sending HTTP request', array('url' => '$url'))
                ->once()->ordered()
            ->getMock();

        $exception = \Mockery::mock(
            'Guzzle\\Http\\Exception\\ClientErrorResponseException'
        );

        $exception
            ->shouldReceive('getResponse->getStatusCode')
                ->withNoArgs()
                ->andReturn('$statusCode');

        $request = \Mockery::mock('Guzzle\\Http\\Message\\RequestInterface')
            ->shouldReceive('send')
                ->withNoArgs()
                ->andThrow($exception)
            ->getMock();

        $guzzleClient = \Mockery::mock('Guzzle\\Http\\ClientInterface')
            ->shouldReceive('get')
                ->with('$url')
                ->andReturn($request)
            ->getMock();

        $daemon = $this->daemon('request')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('guzzleClient')
                ->withNoArgs()
                ->andReturn($guzzleClient)
            ->shouldReceive('handleGuzzleException')
                ->with($exception)
                ->once()->ordered()
            ->getMock();

        $daemon->request('$url');

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::request
     */
    public function testRequestWithUnknownGuzzleError()
    {
        $logger = \Mockery::mock('Psr\\Log\\LoggerInterface')
            ->shouldReceive('info')
                ->with('Sending HTTP request', array('url' => '$url'))
                ->once()->ordered()
            ->getMock();

        $exception = \Mockery::mock(
            'Exception,Guzzle\\Common\\Exception\\GuzzleException'
        );

        $request = \Mockery::mock('Guzzle\\Http\\Message\\RequestInterface')
            ->shouldReceive('send')
                ->withNoArgs()
                ->andThrow($exception)
            ->getMock();

        $guzzleClient = \Mockery::mock('Guzzle\\Http\\ClientInterface')
            ->shouldReceive('get')
                ->with('$url')
                ->andReturn($request)
            ->getMock();

        $daemon = $this->daemon('request')
            ->shouldReceive('logger')
                ->withNoArgs()
                ->andReturn($logger)
            ->shouldReceive('guzzleClient')
                ->withNoArgs()
                ->andReturn($guzzleClient)
            ->shouldReceive('handleGuzzleException')
                ->with($exception)
                ->once()->ordered()
            ->getMock();

        $daemon->request('$url');

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::handleGuzzleException
     */
    public function testHandleGuzzleException()
    {
        $exception = \Mockery::mock()
            ->shouldReceive('getMessage')
                ->withNoArgs()
                ->andReturn('$message')
            ->getMock();

        $daemon = $this->daemon('handleGuzzleException');
        $daemon
            ->shouldReceive('logger->error')
                ->with('Unknown error occurred during HTTP request: $message')
                ->once()->ordered();

        $daemon->handleGuzzleException($exception);

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Daemon::guzzleClient
     */
    public function testGuzzleClient()
    {
        $this->assertInstanceOf(
            'Guzzle\\Http\\ClientInterface',
            $this->daemon('guzzleClient')->guzzleClient()
        );
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
        return $this->mock(
            'BradFeehan\\Rainmaker\\Daemon',
            func_get_args(),
            array(\Mockery::mock('BradFeehan\\Rainmaker\\Configuration'))
        );
    }
}

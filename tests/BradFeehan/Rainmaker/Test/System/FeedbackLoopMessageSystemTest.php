<?php

namespace BradFeehan\Rainmaker\Test\System;

use BradFeehan\Rainmaker\FeedbackLoopMessage;
use BradFeehan\Rainmaker\Test\SystemTestCase;
use Zend\Mail\Storage\Message;

/**
 * @coversNothing
 * @group system
 */
class FeedbackLoopMessageTest extends SystemTestCase
{

    public function testSampleMessage()
    {
        $source = new Message(array(
            'file' => $this->fixturesPath('sample-arf.eml'),
        ));

        $message = new FeedbackLoopMessage($source);

        $this->assertSame(
            array('http://example.com/unsubscribe?id=test@example.org'),
            $message->getUnsubscribeUrls()
        );
    }
}

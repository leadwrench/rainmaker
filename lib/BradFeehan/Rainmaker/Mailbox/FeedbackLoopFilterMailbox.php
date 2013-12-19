<?php

namespace BradFeehan\Rainmaker\Mailbox;

use BradFeehan\Rainmaker\FeedbackLoopMessage;
use BradFeehan\Rainmaker\MailboxInterface;
use FilterIterator;
use Zend\Mail\Storage\Message;

/**
 * Filters only feedback loop messages out of another iterator
 *
 * This simply filters the e-mail messages returned by another iterator
 * to only iterate over the messages that are actually feedback loop
 * messages.
 */
class FeedbackLoopFilterMailbox extends FilterIterator implements MailboxInterface
{

    /**
     * {@inheritdoc}
     *
     * Only accepts messages that are feedback reports.
     */
    public function accept()
    {
        // If it's not a message, it's definitely not a feedback report
        if (!($this->original() instanceof Message)) {
            return false;
        }

        // Retrieve the Content-Type of the current message
        $contentType = $this->original()->getHeader('Content-Type');

        return (
            $contentType->getType() == 'multipart/report' &&
            $contentType->getParameter('report-type') == 'feedback-report'
        );
    }

    /**
     * {@inheritdoc}
     *
     * This wraps the returned Zend messages in a FeedbackLoopMessage
     *
     * @return BradFeehan\Rainmaker\FeedbackLoopMessage
     */
    public function current()
    {
        return new FeedbackLoopMessage($this->original());
    }

    /**
     * Returns the current message from the wrapped mailbox (Iterator)
     *
     * @return Zend\Mail\Storage\Message
     */
    public function original()
    {
        return $this->getInnerIterator()->current();
    }
}

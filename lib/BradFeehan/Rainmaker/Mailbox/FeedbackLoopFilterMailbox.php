<?php

namespace BradFeehan\Rainmaker\Mailbox;

use BradFeehan\Rainmaker\FeedbackLoopMessage;
use BradFeehan\Rainmaker\MailboxInterface;
use Countable;
use FilterIterator;
use Iterator;
use Zend\Mail\Storage\Message;

/**
 * Filters only feedback loop messages out of another iterator
 *
 * This simply filters the e-mail messages returned by another iterator
 * to only iterate over the messages that are actually feedback loop
 * messages.
 */
class FeedbackLoopFilterMailbox extends FilterIterator implements MailboxInterface, Countable
{

    /**
     * The number of messages in this mailbox
     *
     * @var integer
     */
    private $count;

    /**
     * The name of this mailbox
     *
     * @var string
     */
    private $name;


    /**
     * Initializes a new Mailbox from an inner iterator and name
     *
     * @param Iterator $innerIterator The mailbox to filter
     * @param string   $name          The name of this mailbox
     */
    public function __construct(Iterator $innerIterator, $name)
    {
        $this->name = $name;
        parent::__construct($innerIterator);
    }

    /**
     * Retrieves the name of this mailbox
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

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

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = 0;

            foreach ($this as $item) {
                $this->count++;
            }
        }

        return $this->count;
    }
}

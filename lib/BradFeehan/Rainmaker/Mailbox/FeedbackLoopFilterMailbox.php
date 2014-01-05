<?php

namespace BradFeehan\Rainmaker\Mailbox;

use BradFeehan\Rainmaker\FeedbackLoopMessage;
use BradFeehan\Rainmaker\MailboxInterface;
use Countable;
use FilterIterator;
use Iterator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
     * The logger associated with this mailbox
     *
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

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
    public function __construct(Iterator $innerIterator, $name, LoggerInterface $logger = null)
    {
        $this->name = $name;
        $this->logger = $logger ?: new NullLogger();
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
        $this->logger->debug('FeedbackLoopFilterMailbox::accept()');

        // If it's not a message, it's definitely not a feedback report
        if (!($this->original() instanceof Message)) {
            $this->logger->warning(
                'Ignoring non-Message returned from mailbox',
                array('className' => get_class($this->original()))
            );
            return false;
        }

        // Retrieve the Content-Type of the current message
        try {
            $contentType = $this->original()->getHeader('Content-Type');
            $type = $contentType->getType();
        } catch (\Zend\Mail\Storage\Exception\InvalidArgumentException $e) {
            $this->logger->debug(
                'Ignoring message with missing Content-Type header',
                array('className' => get_class($this->original()))
            );

            return false;
        }

        // Must have "multipart/report" content type
        if ($type !== 'multipart/report') {
            $this->logger->debug(
                "The message's Content-Type is not 'multipart/report'",
                array('Content-Type' => $contentType->toString())
            );
            return false;
        }

        try {
            $reportType = $contentType->getParameter('report-type');
        } catch (\Zend\Mail\Storage\Exception\InvalidArgumentException $e) {
            $this->logger->debug(
                'Ignoring message with missing Report-Type',
                array('Content-Type' => $contentType->toString())
            );

            return false;
        }

        // Must have "report-type" set to "feedback-report"
        if ($reportType !== 'feedback-report') {
            $this->logger->debug(
                "The report's Report-Type is not 'feedback-report'",
                array(
                    'Report-Type' => $contentType->getParameter('report-type'),
                )
            );
            return false;
        }

        return true;
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

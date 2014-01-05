<?php

namespace BradFeehan\Rainmaker;

use BradFeehan\Rainmaker\Configuration;
use BradFeehan\Rainmaker\Executer;
use BradFeehan\Rainmaker\ExecuterInterface;

/**
 * Manages the running Rainmaker instance
 */
class Daemon
{

    /**
     * The Configuration for this Daemon
     *
     * @var BradFeehan\Rainmaker\Configuration
     */
    private $configuration;

    /**
     * The Executer instance that this Daemon uses to run
     *
     * @var BradFeehan\Rainmaker\ExecuterInterface
     */
    private $executer;

    /**
     * The array of configured mailboxes
     *
     * @var array
     */
    private $mailboxes;


    /**
     * Initializes the Daemon with a Configuration and an Executer
     *
     * @param BradFeehan\Rainmaker\Configuration     $configuration
     * @param BradFeehan\Rainmaker\ExecuterInterface $executer
     */
    public function __construct(Configuration $configuration, ExecuterInterface $executer = null)
    {
        $this->configuration = $configuration;
        $this->executer = $executer;
        $this->mailboxes = array();
    }

    /**
     * Starts this Daemon
     */
    public function start()
    {
        $this->logger()->debug('Daemon::start()');
        $this->getExecuter()->start();
    }

    /**
     * Retrieves the Configuration for this Daemon
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Retrieves the Executer for this Daemon
     *
     * @return BradFeehan\Rainmaker\ExecuterInterface
     */
    public function getExecuter()
    {
        if ($this->executer === null) {
            $this->executer = new Executer(
                array($this, 'tick'),
                $this->getConfiguration()->get('interval'),
                $this->getConfiguration()->logger()
            );
        }

        return $this->executer;
    }

    /**
     * Retrieves the logger for this Daemon
     *
     * @return Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return $this->getConfiguration()->logger();
    }

    /**
     * The "tick" function for the Executer of this Daemon
     *
     * This is called periodically by this Daemon's Executer.
     */
    public function tick()
    {
        $this->logger()->debug('Daemon::tick()');

        $this->logger()->info('Check starting');

        // Iterate over all mailboxes
        foreach ($this->getConfiguration()->getMailboxes() as $mailbox) {
            $this->logger()->info(
                "Checking mailbox '{$mailbox->getName()}'..."
            );

            // Refresh the mailbox
            $mailbox->refresh();

            $this->logger()->notice(
                "Found {$mailbox->count()} feedback loop messages"
            );

            // Iterate over all feedback report messages in the mailbox
            foreach ($mailbox as $feedbackLoopMessage) {
                $this->processMessage($feedbackLoopMessage);
            }
        }

        $this->logger()->info('Check successfully completed');
    }

    /**
     * Processes an individual message
     *
     * @param BradFeehan\Rainmaker\FeedbackLoopMessage $message
     */
    public function processMessage($message)
    {
        // TODO
    }
}

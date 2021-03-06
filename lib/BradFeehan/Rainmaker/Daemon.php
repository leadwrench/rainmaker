<?php

namespace BradFeehan\Rainmaker;

use BradFeehan\Rainmaker\Configuration;
use BradFeehan\Rainmaker\Executer;
use BradFeehan\Rainmaker\ExecuterInterface;
use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

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
     * The Guzzle HTTP client used by this Daemon
     *
     * @var Guzzle\Http\ClientInterface
     */
    private $guzzleClient;

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

            // Print count of all messages in current folder
            $total = $mailbox->getInnerIterator()->countMessages();
            $this->logger()->info("Found {$total} messages in total");

            // Print count of feedback loop messages specifically
            $count = $mailbox->count();
            $this->logger()->notice("Found {$count} feedback reports");

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
        try {
            $subject = $message->getSource()->getHeader('Subject')->toString();
        } catch (\Zend\Mail\Storage\Exception\InvalidArgumentException $e) {
            $subject = '(No subject)';
        }

        $this->logger()->info(
            "Processing feedback report",
            array('subject' => $subject)
        );

        $urls = $message->getUnsubscribeUrls();
        $this->logger()->notice(
            "Found URLs: '" . implode("', '", $urls) . "'."
        );

        // Send requests to any HTTP unsubscribe URLs
        foreach ($urls as $url) {
            if (preg_match('#^https?://#', $url)) {
                $this->request($url);
            }
        }
    }

    /**
     * Requests a URL via HTTP
     *
     * @param string $url The URL to request
     */
    public function request($url)
    {
        $this->logger()->info('Sending HTTP request', array('url' => $url));

        try {
            $this->guzzleClient()->get($url)->send();
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                // Log a warning if it was "only" a 404
                $this->logger()->warning(
                    "Unsubscribe URL returned HTTP 404 Not Found",
                    array('url' => $url)
                );
            } else {
                // Handle other client errors more severely
                $this->handleGuzzleException($e);
            }
        } catch (GuzzleException $e) {
            $this->handleGuzzleException($e);
        }
    }

    /**
     * Handles unknown Guzzle exceptions
     *
     * @param Guzzle\Common\Exception\GuzzleException $exception
     */
    public function handleGuzzleException($exception)
    {
        $this->logger()->error(
            'Unknown error occurred during HTTP request: ' .
            $exception->getMessage()
        );
    }

    /**
     * Retrieves the Guzzle HTTP client to use for this daemon
     *
     * @return Guzzle\Http\ClientInterface
     */
    public function guzzleClient()
    {
        if (!$this->guzzleClient) {
            $this->guzzleClient = new Client();
        }

        return $this->guzzleClient;
    }
}

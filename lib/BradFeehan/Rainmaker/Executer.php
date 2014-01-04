<?php

namespace BradFeehan\Rainmaker;

use BradFeehan\Rainmaker\Exception\InvalidArgumentException;
use BradFeehan\Rainmaker\ExecuterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Runs code periodically (in a loop)
 */
class Executer implements ExecuterInterface
{

    /**
     * The logger that's used by this executer
     *
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * A flag that determines whether the executer is running or not
     *
     * @var boolean
     */
    protected $running;

    /**
     * The duration (in seconds) for each sleep cycle of this executer
     *
     * @var integer
     */
    protected $sleepDuration;

    /**
     * The Callable that is called each "tick" of the executer
     *
     * @var Callable
     */
    protected $tickFunction;


    /**
     * {@inheritdoc}
     */
    public function __construct($tickFunction, $sleepDuration = 1, LoggerInterface $logger = null)
    {
        if (!is_callable($tickFunction)) {
            throw new InvalidArgumentException(
                'Executer tick function must be Callable'
            );
        }

        if (!is_numeric($sleepDuration) || $sleepDuration < 0) {
            throw new InvalidArgumentException(
                'Sleep duration must be a positive integer'
            );
        }

        $this->tickFunction = $tickFunction;
        $this->sleepDuration = $sleepDuration;
        $this->logger = $logger;
    }

    /**
     * Determines whether the executer is running or not
     *
     * @return boolean
     */
    public function isRunning()
    {
        return (boolean) $this->running;
    }

    /**
     * Retrieves the logger to use for this executer
     *
     * @return Psr\Log\LoggerInterface
     */
    public function logger()
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * {@inheritdoc}
     *
     * This loop is difficult to test, because the only way to exit
     * from the loop would be to set $this->running to false. But that
     * can't be done reliably in the test environment, since we may not
     * have support for forking (which is required for concurrency).
     *
     * @codeCoverageIgnore
     */
    public function start()
    {
        $this->logger()->debug('Executer::start()');

        $this->logger()->notice('Executer starting up');
        $this->running = true;
        $this->setupSignalHandlers();

        while ($this->isRunning()) {
            $this->tick();
            $this->sleep();
        }

        $this->logger()->notice('Executer shutting down');
    }

    /**
     * Does the actual action for the executer
     *
     * This is run every "tick" of the executer, which is every time
     * the sleep duration expires.
     */
    protected function tick()
    {
        $this->logger()->debug('Executer::tick()');
        return call_user_func($this->tickFunction);
    }

    /**
     * Sleeps the process for the sleep duration from the constructor
     *
     * This function is called during the main loop, to sleep after
     * performing the "tick" action.
     */
    protected function sleep()
    {
        $this->logger()->debug('Executer::sleep()');

        $this->logger()->info(
            "Sleeping for {$this->sleepDuration} seconds"
        );

        usleep($this->sleepDuration * 1000000);
    }

    /**
     * Configures signal handlers to gracefully shutdown the daemon
     *
     * This method is difficult to test reliably, because it will
     * behave differently depending on whether the PCNTL functions are
     * available or not in the currently-running PHP.
     *
     * @codeCoverageIgnore
     */
    protected function setupSignalHandlers()
    {
        $this->logger()->debug('Executer::setupSignalHandlers()');

        // Nothing we can do if PCNTL isn't available
        if (!function_exists('pcntl_signal')) {
            $this->logger()->warning(
                'No pcntl_signal() function available: ' .
                'cannot terminate gracefully on signal'
            );

            return;
        }

        declare(ticks = 1);
        pcntl_signal(SIGINT, array($this, 'signalHandlerCallback'));
        pcntl_signal(SIGTERM, array($this, 'signalHandlerCallback'));

        $this->logger()->info('Successfully set up signal handlers');
    }

    /**
     * The callback that gets called when a signal is received
     *
     * @param integer $signalNumber The number of the received signal
     */
    public function signalHandlerCallback($signalNumber = 0)
    {
        $this->logger()->debug(
            'Executer::signalHandlerCallback()',
            array('$signalNumber' => $signalNumber)
        );

        $this->running = false;

        $signals = array(
            0  => 'unknown signal',
            2  => 'SIGINT',
            15 => 'SIGTERM',
        );

        $signalName = $signals[$signalNumber];

        $this->logger()->notice(
            "Received $signalName, gracefully terminating..."
        );
    }
}

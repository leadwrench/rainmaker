<?php

namespace BradFeehan\Rainmaker;

use Psr\Log\LoggerInterface;

/**
 * An interface to a class that can periodically execute a callback
 */
interface ExecuterInterface
{

    /**
     * Note: While "Callable" is a valid type hint in PHP 5.4, this
     * library must be compatible with PHP 5.3, so it can't use the
     * type hint for $tickFunction.
     *
     * @param Callable                $tickFunction  Called each "tick"
     * @param integer                 $sleepDuration Sleep duration (s)
     * @param Psr\Log\LoggerInterface $logger        Logger to use
     */
    public function __construct($tickFunction, $sleepDuration = 1, LoggerInterface $logger = null);

    /**
     * Starts the executer
     *
     * This runs the $tickFunction from the constructor periodically.
     */
    public function start();
}

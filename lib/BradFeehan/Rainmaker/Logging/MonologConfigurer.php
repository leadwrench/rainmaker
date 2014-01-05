<?php

namespace BradFeehan\Rainmaker\Logging;

use BradFeehan\Rainmaker\Exception\InvalidArgumentException;
use BradFeehan\Rainmaker\Logging\ConfigurerInterface;
use BradFeehan\Rainmaker\Utilities;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * Sets up a Monolog logger for use with Rainmaker
 */
class MonologConfigurer implements ConfigurerInterface
{

    /**
     * {@inheritdoc}
     */
    public function createLogger($loggerClassName)
    {
        if (!is_a('Monolog\\Logger', $loggerClassName, true)) {
            throw new InvalidArgumentException(
                "MonologConfigurer can only create a Monolog logger"
            );
        }

        // Instantiate logger
        $loggerClass = new ReflectionClass($loggerClassName);
        $logger = $loggerClass->newInstance('rainmaker');
        return $this->configure($logger);
    }

    /**
     * Configures the logger once it's been created
     *
     * @param Psr\Log\LoggerInterface $logger The logger to configure
     *
     * @return Psr\Log\LoggerInterface The configured logger
     */
    public function configure(Logger $logger)
    {
        // Avoid a PHP warning about not having a default timezone set
        // This is required beforet the logger can be used, as it will
        // print timestamps on each line. If the timezone isn't set, a
        // PHP warning is generated at that point.
        Utilities::setDefaultTimezone();

        // Add a handler to output the messages
        $logger->pushHandler($this->getHandler());

        return $logger;
    }

    /**
     * Retrieves the handler to use with the logger
     *
     * @return Monolog\Handler\HandlerInterface
     */
    public function getHandler()
    {
        // Output messages to stderr by default
        $handler = new StreamHandler('php://stderr', Logger::DEBUG);

        // Sanitize any context keys which have sensitive data in them
        $handler->pushProcessor(new SanitizationProcessor());

        return $handler;
    }
}

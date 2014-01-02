<?php

namespace BradFeehan\Rainmaker\Logging;

/**
 * An object which is used to configure a logger in a particular way
 *
 * This can be extended and used to configure logging in a highly
 * customisable way.
 *
 * As an example, the MonologConfigurer is the default configurer, and
 * is used in conjunction with a Monolog\Logger to configure it to work
 * in the default way.
 *
 * So, in order to replace the logger used, the configuration allows
 * changing the class of the logger. But the MonologConfigurer will
 * also need to be replaced, as it only knows how to configure a
 * Monolog\Logger, not the custom/new class.
 */
interface ConfigurerInterface
{

    /**
     * Creates a logger of a particular class
     *
     * @param string $loggerClassName The class of the logger to create
     *
     * @return Psr\Log\LoggerInterface
     */
    public function createLogger($loggerClassName);
}

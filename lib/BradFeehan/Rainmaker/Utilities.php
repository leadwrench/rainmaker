<?php

namespace BradFeehan\Rainmaker;

class Utilities
{

    /**
     * Sets a default PHP timezone for the current process
     *
     * This checks if a timezone has been set in PHP's configuration,
     * and if not, sets a default value.
     *
     * @param string $default The default timezone to set if none set
     *                        (optional, defaults to "UTC")
     */
    public static function setDefaultTimezone($default = 'UTC')
    {
        if (!ini_get('date.timezone')) {
            // TODO: Use a real logging solution here
            echo "Warning: 'date.timezone' not set in php.ini, defaulting to $default\n";
            ini_set('date.timezone', $default);
        }
    }
}

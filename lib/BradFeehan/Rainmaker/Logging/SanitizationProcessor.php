<?php

namespace BradFeehan\Rainmaker\Logging;

use Monolog\Logger;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Zend\Stdlib\ArrayObject;

/**
 * A Monolog Processor which hides sensitive information in log output
 *
 * This Processor uses a heuristic to identify data in the $context
 * array which might be sensitive, and then replaces the value of all
 * detected sensitive fields with "***".
 *
 * By default, the heuristic works by treating any fields whose key
 * contains either "password" or "key" anywhere within the key as
 * sensitive, and replaces the value with "***".
 */
class SanitizationProcessor
{

    /**
     * Regular expressions to match agains keys in the $context array
     *
     * Determines which keys in the $context array to sanitize
     *
     * @var array
     */
    private $patterns;

    /**
     * The log level for this processor
     *
     * @var integer
     */
    private $level;


    /**
     * Initializes a new processor for a log level and patterns
     *
     * Any log messages below the log level for this processor will be
     * ignored and returned untouched.
     *
     * The $patterns array defines a set of regular expressions. All
     * keys in the $context array whose key matches one of these
     * patterns will have its value sanitized.
     *
     * @param integer $level    The log level for this processor
     * @param array   $patterns Regexes to match against $context keys
     *                          (optional, if omitted the default value
     *                          will be array('/password/', '/key/'),
     *                          which will match any keys which contain
     *                          the word "password" or "key" anywhere).
     */
    public function __construct($level = Logger::DEBUG, array $patterns = null)
    {
        $this->level = $level;
        $this->patterns = $patterns ?: array('/password/', '/key/');
    }

    /**
     * Invokes this processor on a record
     *
     * @param array $record The record to process
     *
     * @return array The processed record
     */
    public function __invoke(array $record)
    {
        // Return if the level is too low for this processor
        if ($record['level'] < $this->level) {
            return $record;
        }

        // Recursively sanitize all elements in the $context array
        $context = new RecursiveArrayIterator($record['context']);
        foreach ($context as $key => $value) {
            // Check if the key for this element matches any patterns
            foreach ($this->patterns as $pattern) {
                if (preg_match($pattern, $key)) {
                    // Pattern matched, do sanitisation
                    $context->offsetSet($key, '***');
                    break;
                }
            }
        }

        $record['context'] = $context->getArrayCopy();

        return $record;
    }
}

<?php

namespace BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe;

use Zend\Mail\Header\GenericHeader;

/**
 * Parses the value of a List-Unsubscribe header
 */
interface ParserInterface
{

    /**
     * Initializes a new Parser instance from a List-Unsubscribe header
     *
     * @param Zend\Mail\Header\GenericHeader $header The header
     */
    public function __construct(GenericHeader $header);

    /**
     * Retrieves the URLs from the List-Unsubscribe header
     *
     * Should return an numerically-indexed array, containing each URL
     * as a string.
     *
     * @return array
     */
    public function parse();
}

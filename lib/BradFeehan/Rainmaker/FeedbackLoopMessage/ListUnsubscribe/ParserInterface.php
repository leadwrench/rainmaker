<?php

namespace BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe;

use Zend\Mail\Header\GenericHeader;

/**
 * Parses the value of a List-Unsubscribe header
 */
interface ParserInterface
{

    /**
     * Retrieves the list of URLs from a List-Unsubscribe header
     *
     * Should return an numerically-indexed array, containing each URL
     * as a string. If no unsubscribe URLs could be parsed, an empty
     * array should be returned (in other words, always return an
     * array, or throw an exception if necessary).
     *
     * @param Zend\Mail\Header\GenericHeader $header The header
     *
     * @return array
     */
    public function parse(GenericHeader $header);
}

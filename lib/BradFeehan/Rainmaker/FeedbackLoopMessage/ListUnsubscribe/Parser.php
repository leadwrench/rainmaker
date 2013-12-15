<?php

namespace BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe;

use BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface;
use Zend\Mail\Header\GenericHeader;

/**
 * Parses the value of a List-Unsubscribe header
 *
 * Typically this List-Unsubscribe header would come from a
 * FeedbackLoopMessage. This class parses the value of the header into
 * a PHP array of URLs.
 *
 * See RFC2369: "The Use of URLs as Meta-Syntax for Core Mail List
 * Commands and their Transport through Message Header Fields".
 * Available at http://www.faqs.org/rfcs/rfc2369.html
 */
class Parser implements ParserInterface
{

    /**
     * The List-Unsubscribe header that's being parsed by this Parser
     *
     * @var Zend\Mail\Header\GenericHeader $header
     */
    protected $header;


    /**
     * {@inheritdoc}
     */
    public function __construct(GenericHeader $header)
    {
        $this->header = $header;
    }

    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $raw = $this->header->getFieldValue();

        // "[...] client applications should treat any whitespace, that
        // might be inserted by poorly behaved MTAs, as characters to
        // ignore."
        $raw = preg_replace('/\\s/', '', $raw);

        // "[...] if the content of the field (following any leading
        // whitespace, including comments) begins with any character
        // other than the opening angle bracket '<', the field SHOULD
        // be ignored."
        $raw = preg_replace('/^\\([^)]*\\)/', '', $raw);

        if (strpos($raw, '<') !== 0) {
            return array();
        }

        // "A list of multiple, alternate, URLs MAY be specified by a
        // comma-separated list of angle-bracket enclosed URLs."
        $urls = array();
        foreach (explode(',', $raw) as $token) {
            // "Any characters following an angle bracket enclosed URL
            // SHOULD be ignored"
            if (preg_match("/^<([^>]*)>/", $token, $matches)) {
                $urls[] = $matches[1];
            } else {
                // "If a sub-item (comma-separated item) within the
                // field is not an angle-bracket enclosed URL, the
                // remainder of the field (the current, and all
                // subsequent, sub-items) SHOULD be ignored."
                break;
            }
        }

        return $urls;
    }
}

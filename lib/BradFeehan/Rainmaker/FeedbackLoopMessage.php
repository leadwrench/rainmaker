<?php

namespace BradFeehan\Rainmaker;

use BradFeehan\Rainmaker\Exception\InvalidArgumentException;
use BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\Parser;
use BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface;
use ReflectionClass;
use Zend\Mail\Storage\Exception\InvalidArgumentException as ZendMailInvalidArgumentException;
use Zend\Mail\Storage\Message;

/**
 * Represents a feedback loop report e-mail message
 *
 * This usually represents a feedback report that's been retrieved by
 * Rainmaker from an e-mail account.
 */
class FeedbackLoopMessage
{

    /**
     * The "source" email message of this FeedbackLoopMessage
     *
     * @var Zend\Mail\Storage\Part\PartInterface
     */
    protected $source;

    /**
     * Cache for the parser for this FeedbackLoopMessage
     *
     * @var BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface
     */
    protected $parser;

    /**
     * The default class to instantiate to use for $this->parser
     *
     * @var string
     */
    protected $parserClass = 'BradFeehan\\Rainmaker\\FeedbackLoopMessage\\ListUnsubscribe\\Parser';


    /**
     * Initializes a FeedbackLoopMessage instance
     *
     * Requires a Zend\Mail message to be passed in as the "source" of
     * the FeedbackLoopMessage. This will be the actual retrieved email
     * message retrieved from the server, which this class wraps to add
     * some additional functionality specific to feedback loop
     * messages.
     *
     * Also accepts an optional second argument, $parser. Passing in an
     * ParserInterface instance will override the default parser used
     * to extract unsubscribe URLs from the List-Unsubscribe header.
     *
     * @param Zend\Mail\Storage\Part\PartInterface                                     $source
     * @param BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface $parser
     */
    public function __construct($source, ParserInterface $parser = null)
    {
        $this->source = $source;
        $this->parser = $parser;
    }

    /**
     * Retrieves the parser for this FeedbackLoopMessage
     *
     * @return BradFeehan\Rainmaker\FeedbackLoopMessage\ListUnsubscribe\ParserInterface
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new $this->parserClass();
        }

        return $this->parser;
    }

    /**
     * Retrieves the URLs specified in the List-Unsubscribe header
     *
     * This assumes that the FeedbackLoopMessage contains a copy of the
     * original (reported) message. It looks for any "List-Unsubscribe"
     * headers in the message, and extracts the URLs from the first one
     * it finds.
     *
     * Returns an array of strings, each representing one of the URLs
     * specified in the header.
     *
     * @return array
     */
    public function getUnsubscribeUrls()
    {
        if (!$this->source->isMultipart()) {
            // The FeedbackLoopMessage isn't multipart, so it can't
            // contain a copy of the original message.
            return array();
        }

        // Iterate over each of the multipart parts
        foreach ($this->source as $part) {
            $urls = $this->parsePart($part);
            if ($urls !== null) {
                return $urls;
            }
        }

        // If we reach this point, it's because we didn't find any
        // parts that had a List-Unsubscribe header.
        return array();
    }


    /**
     * Attempts to parse an individual part of the source message
     *
     * This method will analyse the part for any unsubscribe links, and
     * will return any it finds in an array.
     *
     * If the part doesn't have a List-Unsubscribe header at all, or if
     * no unsubscribe links could be found for any other reason, NULL
     * will be returned.
     *
     * If the part does contain a List-Unsubscribe header, but no
     * unsubscribe URLs could be extracted from it, then an empty array
     * will be returned.
     *
     * This method needs to be public for testability, but it should
     * probably be protected.
     *
     * @param Zend\Mail\Storage\Part\PartInterface $part
     *
     * @return array|null
     */
    public function parsePart($part)
    {
        // Skip any parts that aren't messages
        if (strpos($part->contentType, 'message/') !== 0) {
            return null;
        }

        try {
            // Interpret this part's contents as a message
            $message = new Message(array('raw' => $part->getContent()));
            $header = $message->getHeader('List-Unsubscribe');
        } catch (ZendMailInvalidArgumentException $e) {
            // This part didn't have a List-Unsubscribe header
            return null;
        }

        // Parse header using the List-Unsubscribe header parser
        return $this->getParser()->parse($header);
    }
}

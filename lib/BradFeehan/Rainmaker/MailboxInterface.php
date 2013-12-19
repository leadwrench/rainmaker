<?php

namespace BradFeehan\Rainmaker;

use Iterator;

/**
 * Represents a configured e-mail account
 *
 * The main purpose of this class is to retrieve FeedbackLoopMessage
 * objects from an e-mail account. The implementation should be an
 * iterator, which iterates over the FeedbackLoopMessage objects.
 */
interface MailboxInterface extends Iterator
{
}

<?php

namespace BradFeehan\Rainmaker\Test\Unit;

use BradFeehan\Rainmaker\Test\UnitTestCase;
use BradFeehan\Rainmaker\Utilities;

class UtilitiesTest extends UnitTestCase
{

    /**
     * Stores the original timezone, before this test case ran
     *
     * @var string
     */
    private $originalTimezone;


    /**
     * {@inheritdoc}
     *
     * Clear the timezone, saving the value for later.
     */
    public function setUp()
    {
        $this->originalTimezone = $this->getTimezone();
        $this->setTimezone(false);
        $this->assertSame('', $this->getTimezone());
    }

    /**
     * {@inheritdoc}
     *
     * Re-sets the timezone to the value it was at before the test ran
     */
    public function tearDown()
    {
        $this->setTimezone($this->originalTimezone);
    }

    /**
     * @covers BradFeehan\Rainmaker\Utilities::setDefaultTimezone
     */
    public function testSetDefaultTimezoneWithTimezoneAlreadySet()
    {
        $this->setTimezone('Australia/Melbourne');
        Utilities::setDefaultTimezone('UTC');
        $this->assertSame('Australia/Melbourne', $this->getTimezone());
    }

    /**
     * @covers BradFeehan\Rainmaker\Utilities::setDefaultTimezone
     */
    public function testSetDefaultTimezoneWithNoTimezoneSet()
    {
        $this->assertSame('', $this->getTimezone());

        // Use output buffering as a kludge to hide the output
        // This demonstrates one reason to use a real logging solution
        // TODO: Remove this hack once real logging is implemented
        ob_start();
        Utilities::setDefaultTimezone('UTC');
        ob_end_clean();

        $this->assertSame('UTC', $this->getTimezone());
    }


    /**
     * Retrieves the currently configured PHP timezone
     *
     * @return string
     */
    private function getTimezone()
    {
        return ini_get('date.timezone');
    }

    /**
     * Sets PHP's configured timezone to a particular value
     *
     * @param string $value The value to set the timezone to
     */
    private function setTimezone($value)
    {
        @ini_set('date.timezone', $value);
    }
}

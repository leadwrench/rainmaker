<?php

namespace BradFeehan\Rainmaker\Test;

use PHPUnit_Framework_TestCase;
use ReflectionObject;

/**
 * Base class for all test cases in this project
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * Retrieves the path to a particular fixture file
     *
     * This just interprets the path argument as being relative to the
     * fixtures directory ($this->fixturesRoot()), and returns the
     * absolute path to the resulting file.
     *
     * Returns FALSE if the resulting file doesn't exist.
     *
     * @param string $path The path under tests/fixtures to the fixture
     *
     * @return string On success, returns an absolute path as a string;
     *                on failure, returns FALSE.
     */
    protected function fixturesPath($path)
    {
        return realpath($this->fixturesRoot() . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Retrieves the path to the fixtures directory
     *
     * This is tests/fixtures, relative to the project root.
     *
     * @return string
     */
    protected function fixturesRoot()
    {
        $ds = DIRECTORY_SEPARATOR;
        return realpath(__DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}fixtures");
    }
}

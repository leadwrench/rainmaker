<?php

namespace BradFeehan\Rainmaker\Test;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionMethod;
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

    /**
     * Invokes a private or protected method on any object
     *
     * @param object $object     The object to invoke the method on
     * @param string $methodName The name of the method to invoke
     * @param mixed  $arguments  Any additional arguments are used as
     *                           arguments for the method call.
     *
     * @return mixed The return value of the private/protected method
     */
    protected function invokePrivateMethod($object, $methodName/* , [$arg1[, $arg2 ...]]  */)
    {
        // Create a ReflectionObject from the $object
        $objectReflection = new ReflectionObject($object);

        // Retrieve the specified method from the reflection object
        $method = $objectReflection->getMethod($methodName);

        // This is where the magic happens, allowing us to call the
        // method from outside the class
        $method->setAccessible(true);

        // Actually invoke the method and return the return value
        $arguments = array_slice(func_get_args(), 2);
        return $method->invokeArgs($object, $arguments);
    }

    /**
     * Creates a partially-mocked object
     *
     * Allows specifying which methods should NOT be mocked
     *
     * @param string $className            The class to create
     * @param array  $passthroughMethods   Methods to passthrough
     * @param array  $constructorArguments Array of constructor args
     *
     * @return Mockery\MockInterface
     */
    protected function mock($className, $passthroughMethods = array(), $constructorArguments = array())
    {
        // Determine which methods should be mocked
        $mockedMethodNames = array();
        $reflectionClass = new ReflectionClass($className);

        $allMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($allMethods as $method) {
            $methodName = $method->getName();

            // Mock any methods that aren't in the unmocked method list
            if (!in_array($methodName, (array) $passthroughMethods)) {
                $mockedMethodNames[] = $methodName;
            }
        }

        // Create the class as a partial mock
        $methods = implode(',', $mockedMethodNames);
        return \Mockery::mock(
            "{$className}[{$methods}]",
            $constructorArguments
        );
    }
}

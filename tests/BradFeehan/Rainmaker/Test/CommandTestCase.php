<?php

namespace BradFeehan\Rainmaker\Test;

use BradFeehan\Rainmaker\Test\UnitTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTestCase extends UnitTestCase
{

    /**
     * Executes a command using a CommandTester
     *
     * Accepts a Symfony command object as an input, and returns the
     * CommandTester after it has run the command.
     *
     * @param Symfony\Component\Console\Command\Command $command
     *
     * @return Symfony\Component\Console\Tester\CommandTester
     */
    protected function runCommand(Command $command, array $arguments)
    {
        $application = new Application();
        $application->add($command);

        $command = $application->find($command->getName());
        $commandTester = new CommandTester($command);

        $commandTester->execute($arguments);
        return $commandTester;
    }
}

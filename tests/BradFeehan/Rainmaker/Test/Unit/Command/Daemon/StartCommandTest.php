<?php

namespace BradFeehan\Rainmaker\Test\Unit\Command\Daemon;

use BradFeehan\Rainmaker\Command\Daemon\StartCommand;
use BradFeehan\Rainmaker\Test\CommandTestCase;

class StartCommandTest extends CommandTestCase
{

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::configure
     */
    public function testConfigure()
    {
        $command = new StartCommand();

        $this->assertSame('daemon:start', $command->getName());

        $this->assertSame(
            'Start the Rainmaker daemon',
            $command->getDescription()
        );

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Input\\InputArgument',
            $command->getDefinition()->getArgument('config')
        );
    }

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::execute
     */
    public function testExecute()
    {
        $data = array('$foo' => '$bar');
        $configuration = \Mockery::mock('BradFeehan\\Rainmaker\\Configuration');

        $daemon = \Mockery::mock('BradFeehan\\Rainmaker\\Daemon')
            ->shouldReceive('start')
                ->once()
            ->getMock();

        $input = \Mockery::mock(
            'Symfony\\Component\\Console\\Input\\InputInterface'
        );

        $output = \Mockery::mock(
            'Symfony\\Component\\Console\\Output\\OutputInterface'
        );

        $command = $this->mock(
            'BradFeehan\\Rainmaker\\Command\\Daemon\\StartCommand',
            array('execute', 'setName', 'setDescription', 'addArgument')
        );

        $command
            ->shouldReceive('getConfigurationData')
                ->with($input)
                ->andReturn($data)
            ->shouldReceive('createConfiguration')
                ->with($data)
                ->andReturn($configuration)
            ->shouldReceive('createDaemon')
                ->with($configuration)
                ->andReturn($daemon);

        $command->execute($input, $output);

        $this->assertTrue(true);
    }

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::getConfigurationData
     */
    public function testGetConfigurationData()
    {
        $input = \Mockery::mock(
            'Symfony\\Component\\Console\\Input\\InputInterface'
        );

        $ds = DIRECTORY_SEPARATOR;
        $root = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..";
        $configFile = "{$root}{$ds}config{$ds}config.sample.yaml";

        $input
            ->shouldReceive('getArgument')
                ->with('config')
                ->andReturn($configFile);

        $command = new StartCommand();

        $data = array(
            'interval' => 60,
            'mailboxes' => array(
                array(
                    'name' => 'test@example.com',
                    'protocol' => 'imap',
                    'host' => 'imap.example.com',
                    'port' => 993,
                    'ssl' => 'SSL',
                    'user' => 'test@example.com',
                    'password' => 'secret',
                ),
            ),
            'logger' => array(
                'class' => 'Monolog\\Logger',
                'configuration' => array(
                    'class' => 'BradFeehan\\Rainmaker\\Logging\\MonologConfigurer',
                ),
            ),
        );

        $this->assertSame($data, $command->getConfigurationData($input));
    }

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::getConfigurationData
     * @expectedException BradFeehan\Rainmaker\Exception\InvalidArgumentException
     * @expectedExceptionMessage Couldn't find config file
     */
    public function testGetConfigurationDataInvalid()
    {
        $input = \Mockery::mock(
            'Symfony\\Component\\Console\\Input\\InputInterface'
        );

        $input
            ->shouldReceive('getArgument')
                ->with('config')
                ->andReturn(false);

        $command = new StartCommand();

        $command->getConfigurationData($input);
    }

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::createConfiguration
     */
    public function testCreateConfiguration()
    {
        $data = array('$foo' => '$bar');

        $processor = \Mockery::mock(
            'Symfony\\Component\\Config\\Definition\\Processor'
        );

        $processor
            ->shouldReceive('processConfiguration')
                ->with(
                    \Mockery::type('Bradfeehan\\Rainmaker\\Configuration'),
                    array($data)
                )
                ->andReturn(array('$baz' => '$qux'));

        $command = new StartCommand();

        $configuration = $command->createConfiguration($data, $processor);

        $this->assertInstanceOf(
            'BradFeehan\\Rainmaker\\Configuration',
            $configuration
        );

        $this->assertSame(array('$baz' => '$qux'), $configuration->get());
    }

    /**
     * @covers BradFeehan\Rainmaker\Command\Daemon\StartCommand::createDaemon
     */
    public function testCreateDaemon()
    {
        $command = new StartCommand();
        $configuration = \Mockery::mock('BradFeehan\\Rainmaker\\Configuration');

        $daemon = $command->createDaemon($configuration);

        $this->assertInstanceOf('BradFeehan\\Rainmaker\\Daemon', $daemon);
        $this->assertSame($configuration, $daemon->getConfiguration());
    }
}

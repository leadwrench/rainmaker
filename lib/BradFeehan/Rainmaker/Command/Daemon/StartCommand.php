<?php

namespace BradFeehan\Rainmaker\Command\Daemon;

use BradFeehan\Rainmaker\Configuration;
use BradFeehan\Rainmaker\Daemon;
use BradFeehan\Rainmaker\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Starts the daemon running
 */
class StartCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('daemon:start')
            ->setDescription('Start the Rainmaker daemon')
            ->addArgument(
                'config',
                InputArgument::OPTIONAL,
                'Path to a configuration file to use'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->getConfigurationData($input);
        $configuration = $this->createConfiguration($data);
        $daemon = $this->createDaemon($configuration);
        $daemon->start();
    }

    /**
     * Finds the configuration file to use and reads the data from it
     *
     * Looks at the "config" argument in $input, falling back to a
     * default configuration file.
     *
     * @return array
     */
    public function getConfigurationData(InputInterface $input)
    {
        // Try getting the configuration file from the command-line
        $configurationFile = $input->getArgument('config');

        if (!$configurationFile) {
            // Fall back to a default of config/rainmaker.yml
            $ds = DIRECTORY_SEPARATOR;
            $root = realpath(__DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..");
            $configurationFile = $root . "{$ds}config{$ds}rainmaker.yaml";
        }

        if (!file_exists($configurationFile)) {
            throw new InvalidArgumentException(
                "Couldn't find config file '$configurationFile'"
            );
        }

        return Yaml::parse($configurationFile);
    }

    /**
     * Creates the configuration object from configuration data
     *
     * @param array $data Configuration data to use
     *
     * @return BradFeehan\Rainmaker\Configuration
     */
    public function createConfiguration($data, Processor $processor = null)
    {
        $configuration = new Configuration($processor);
        $configuration->process($data);
        return $configuration;
    }

    /**
     * Creates the Daemon used by this Command
     *
     * @param BradFeehan\Rainmaker\Configuration $configuration
     *
     * @return BradFeehan\Rainmaker\Daemon
     */
    public function createDaemon($configuration)
    {
        return new Daemon($configuration);
    }
}

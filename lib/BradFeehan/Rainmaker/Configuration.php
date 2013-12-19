<?php

namespace BradFeehan\Rainmaker;

use BradFeehan\Rainmaker\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * Handles the configuration for Rainmaker
 *
 * Powered by Symfony's Config component.
 */
class Configuration implements ConfigurationInterface
{

    /**
     * The configuration data backing this instance
     *
     * @var array
     */
    private $data;

    /**
     * The processor that is used to parse configuration input
     *
     * @var Symfony\Component\Config\Definition\Processor
     */
    private $processor;


    /**
     * Initializes a new Configuration instance with a Processor
     *
     * @param Symfony\Component\Config\Definition\Processor $processor
     */
    public function __construct(Processor $processor = null)
    {
        $this->data = array();
        $this->processor = $processor ?: new Processor();
    }

    /**
     * Processes one or more config arrays into this instance
     *
     * This takes any number of configuration data arrays as arguments.
     * It merges them all together using the Processor and saves the
     * resulting data into this Configuration instance. This instance
     * will then be able to be used to query for configuration values.
     *
     * @param array $data A configuration data array to process
     *
     * @return BradFeehan\Rainmaker\Configuration $this
     * @chainable
     */
    public function process($data)
    {
        $this->data = $this->processor->processConfiguration(
            $this,
            func_get_args()
        );

        return $this;
    }

    /**
     * Retrieves a configuration item by key, or the whole config array
     *
     * If $key is null, the entire configuration array is returned.
     *
     * @param string $key The key to retrieve
     *
     * @return array
     */
    public function get($key = null)
    {
        if ($key === null) {
            return $this->data;
        }

        if (!isset($this->data[$key])) {
            throw new InvalidArgumentException(
                "Unknown configuration key '$key'"
            );
        }

        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $builder->root('rainmaker')
            ->children()
                ->arrayNode('mailboxes')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->enumNode('protocol')
                                ->values(array(
                                    'imap',
                                    'pop',
                                ))
                            ->end()
                            ->scalarNode('user')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('ssl')->end()
                            ->scalarNode('folder')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}

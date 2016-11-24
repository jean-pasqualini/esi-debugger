<?php

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Http\Cache\Symfony;
use Http\Cache\Iframe;
use Symfony\Component\Config\Definition\Processor;

/**
 * AppConfigurationProcessor
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 */
class AppConfigurationProcessor implements ConfigurationInterface
{
    protected $modes = array(
        'symfony' => array(
            'class' => Symfony::class
        ),
        'iframe' => array(
            'class' => Iframe::class
        )
    );

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('esidebugger');

        $rootNode
            ->children()
                ->scalarNode('baseUrl')
                    ->isRequired()
                ->end()
                ->scalarNode('patternFragment')
                    ->isRequired()
                ->end()
                ->enumNode('mode')
                    ->isRequired(true)
                    ->values(array_keys($this->modes))
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function process(array $configuration)
    {
        $processor = new Processor();
        $configuration = $processor->processConfiguration($this, $configuration);
        $configuration['mode'] = $this->modes[$configuration['mode']];
        return $configuration;
    }
}
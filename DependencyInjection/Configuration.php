<?php

namespace DGC\MongoODMBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dgc_mongo_odm');

        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('uri')->end()
                            ->arrayNode('uriOptions')
                                ->children()
                                    ->scalarNode('appname')->end()
                                    ->scalarNode('authSource')->end()
                                    ->booleanNode('canonicalizeHostname')->end()
                                    ->integerNode('connectTimeoutMS')->end()
                                    ->scalarNode('gssapiServiceName')->end()
                                    ->integerNode('heartbeatFrequencyMS')->end()
                                    ->booleanNode('journal')->end()
                                    ->integerNode('localThresholdMS')->end()
                                    ->integerNode('maxStalenessSeconds')->end()
                                    ->scalarNode('password')->end()
                                    ->scalarNode('readConcernLevel')->end()
                                    ->scalarNode('readPreference')->end()
                                    ->arrayNode('readPreferenceTags')->end()
                                    ->scalarNode('replicaSet')->end()
                                    ->integerNode('serverSelectionTimeoutMS')->end()
                                    ->booleanNode('serverSelectionTryOnce')->end()
                                    ->integerNode('socketCheckIntervalMS')->end()
                                    ->integerNode('socketTimeoutMS')->end()
                                    ->booleanNode('ssl')->end()
                                    ->scalarNode('username')->end()
                                    ->scalarNode('w')->end()
                                    ->scalarNode('wTimeoutMS')->end()
                                ->end()
                            ->end()
                            ->arrayNode('driverOptions')
                                ->children()
                                    ->booleanNode('allow_invalid_hostname')->end()
                                    ->scalarNode('ca_dir')->end()
                                    ->scalarNode('ca_file')->end()
                                    ->scalarNode('crl_file')->end()
                                    ->scalarNode('pem_file')->end()
                                    ->scalarNode('pem_pwd')->end()
                                    ->booleanNode('weak_cert_validation')->end()
                                ->end()
                            ->end()
                            ->scalarNode('default_database')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_database')->end()
                ->scalarNode('proxy_dir')->defaultValue('dgc/mongo-odm/Proxy')->end()
                ->scalarNode('proxy_namespace')->defaultValue('DGC\MongoODMBundle\Proxy')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

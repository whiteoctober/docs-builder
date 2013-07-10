<?php

namespace WhiteOctober\DocsBuilder\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigFileConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("docs_builder");

        $rootNode->
            addDefaultsIfNotSet()->
            children()->
                arrayNode("github")->
                    isRequired()->
                    children()->
                        scalarNode("api_token")->defaultValue("")->end()->
                        scalarNode("organisation")->defaultValue("")->end()->
                        scalarNode("username")->defaultValue("")->end()->
                    end()->
                    validate()->
                        ifTrue(function($github) {
                            return (!strlen($github["organisation"]) && !strlen($github["username"]));
                        })->thenInvalid("One of Github organisation or username must be supplied")->
                    end()->
                end()->
                scalarNode("docs_file")->isRequired()->cannotBeEmpty()->end()->
            end()
        ;

        return $treeBuilder;
    }
}




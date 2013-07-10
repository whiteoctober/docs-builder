<?php

namespace WhiteOctober\DocsBuilder\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DocFileConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("docs");

        $rootNode->
            addDefaultsIfNotSet()->
            children()->
                booleanNode("phpdoc")->defaultFalse()->end()->
                booleanNode("sphinx")->defaultFalse()->end()->
            end()
        ;

        return $treeBuilder;
    }
}




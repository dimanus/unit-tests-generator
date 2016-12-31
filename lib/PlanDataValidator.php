<?php

namespace Kassko\Test\UnitTestsGenerator;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/*
kassko_unit_tests_generator:
    properties:
        propOne:
            name: ~
            config: ~
    methods:
        methOne:
            name: ~
            config: ~
*/

class PlanDataValidator implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('kassko_unit_tests_generator');

        $rootNode
            ->append($this->addPropertiesNode())
            ->append($this->addMethodsNode())
        ;

        return $builder;
    }

    protected function addPropertiesNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('properties');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                ->end()
                ->append($this->addPropertyConfigNode())
            ->end()
        ;

        return $rootNode;
    }

    protected function addPropertyConfigNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('config');

        $rootNode
            //->canBeEnabled()
            ->prototype('array')
                ->children()
                    ->enumNode('type')
                        ->values([null, 'address'])
                        ->defaultValue(null)
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    protected function addMethodsNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('methods');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->append($this->addMethodConfigNode())
            ->end()
        ;

        return $rootNode;
    }

    protected function addMethodConfigNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('config');

        $rootNode
            ->canBeEnabled()
            ->children()
                ->arrayNode('expectations')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('return')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('spies')
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('mocks')->requiresAtLeastOneElement()->prototype('scalar')->end()->end()
                            ->scalarNode('enabled')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('mocks_store')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('return')->cannotBeEmpty()->end()
                            ->scalarNode('enabled')->defaultTrue()->end()
                        ->end()
                        ->append($this->addExpressionNode())
                        ->append($this->addMockBehaviourNode())
                    ->end()
                ->end()

                ->arrayNode('spie_store')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('enabled')->defaultTrue()->end()
                        ->end()
                        ->append($this->addSpyKindNode('expected'))
                        ->append($this->addMethodExprNode())
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    protected function addExpressionNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('expr');

        $node
            ->children()
                ->enumNode('type')
                    ->values(['method, opposite_of_mock, mocks'])
                    ->isRequired()
                ->end()
                ->arrayNode('config')
                    ->children()
                    ->end()
                    ->append($this->addMethodExprNode())
                    ->append($this->addOppositeOfMockExprNode())
                    ->append($this->addMocksExprNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function addMethodExprNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('method');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('obj')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('member')->defaultTrue()->end()
                ->scalarNode('func')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    protected function addOppositeOfMockExprNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('opposite_of_mock');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    protected function addMocksExprNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('mocks');

        $node
            ->canBeEnabled()
            ->children()
                ->arrayNode('items')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function addMockBehaviourNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('behav');

        $node
            ->children()
                ->enumNode('type')
                    ->values(['noop, return, return_instance_of'])
                    ->isRequired()
                ->end()
                ->arrayNode('config')
                    ->children()
                    ->end()
                    ->append($this->addNoopBehavNode())
                    ->append($this->addRetValBehavNode())
                    ->append($this->addRetInstanceOfBehavNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function addNoopBehavNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('noop');

        $node
            ->canBeEnabled()
        ;

        return $node;
    }

    protected function addRetValBehavNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('return');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    protected function addRetInstanceOfBehavNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('return_instance_of');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    protected function addSpyKindNode($name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->children()
                ->enumNode('type')
                    ->values(['calls, exception'])
                    ->isRequired()
                ->end()
                ->arrayNode('config')
                    ->children()
                    ->end()
                    ->append($this->addCallsSpyKindNode())
                    ->append($this->addExceptionSpyKindNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function addCallsSpyKindNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('calls');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }

    protected function addExceptionSpyKindNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('exception');

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                ->integerNode('code')->end()
                ->scalarNode('message')->cannotBeEmpty()->end()
            ->end()
        ;

        return $node;
    }
}

<?php

namespace Sulu\Bundle\CommunityBundle\DependencyInjection;

use Sulu\Bundle\CommunityBundle\Form\Type\LoginType;
use Sulu\Bundle\CommunityBundle\Form\Type\RegistrationType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const WEBSPACES = 'webspaces';

    // Basic Webspace Configuration
    const MAIL_FROM = 'from';
    const MAIL_TO = 'to';
    const ROLE = 'role';

    // Form Types
    const TYPE_LOGIN = 'login';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_CONFIRMATION = 'confirmation';

    public static $TYPES = [
        self::TYPE_LOGIN,
        self::TYPE_REGISTRATION,
        self::TYPE_CONFIRMATION,
    ];

    // Form Configuration
    const FORM_TEMPLATE = 'template';
    const FORM_TYPE = 'type';
    const FORM_TYPE_OPTIONS = 'options';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sulu_community');

        $rootNode
            ->children()
                ->arrayNode('webspaces')
                    ->prototype('array')
                        ->children()
                            // Basic Webspace Configuration
                            ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                            ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                            ->scalarNode(self::ROLE)->defaultValue('Website')->end()
                            // Login
                            ->arrayNode(self::TYPE_LOGIN)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login Form Configuration
                                    ->scalarNode(self::FORM_TEMPLATE)->defaultValue('community/login.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(LoginType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('validation_groups')->defaultValue('login')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Registration
                            ->arrayNode(self::TYPE_REGISTRATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login Form Configuration
                                    ->scalarNode(self::FORM_TEMPLATE)->defaultValue('community/registration.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(RegistrationType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('validation_groups')->defaultValue('registration')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

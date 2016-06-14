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

    // Basic Webspace configuration
    const EMAIL_FROM = 'from';
    const EMAIL_TO = 'to';
    const ROLE = 'role';
    const FIREWALL = 'firewall';

    // Form types
    const TYPE_LOGIN = 'login';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_CONFIRMATION = 'confirmation';

    public static $TYPES = [
        self::TYPE_LOGIN,
        self::TYPE_REGISTRATION,
        self::TYPE_CONFIRMATION,
    ];

    // Form configuration
    const FORM_TEMPLATE = 'template';
    const FORM_TYPE = 'type';
    const FORM_TYPE_OPTIONS = 'options';

    // Other type configurations
    const ACTIVATE_USER = 'activate_user';
    const AUTO_LOGIN = 'auto_login';
    const REDIRECT_TO = 'redirect_to';
    const EMAIL = 'email';
    const EMAIL_SUBJECT = 'subject';
    const EMAIL_ADMIN_TEMPLATE = 'admin_template';
    const EMAIL_USER_TEMPLATE = 'user_template';

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
                            ->scalarNode(self::EMAIL_FROM)->defaultValue(null)->end()
                            ->scalarNode(self::EMAIL_TO)->defaultValue(null)->end()
                            ->scalarNode(self::ROLE)->defaultValue(null)->end()
                            ->scalarNode(self::FIREWALL)->defaultValue(null)->end()
                            // Login
                            ->arrayNode(self::TYPE_LOGIN)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login Form Configuration
                                    ->scalarNode(self::FORM_TEMPLATE)->defaultValue('community/login.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(LoginType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->addDefaultsIfNotSet()
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
                                    ->end()
                                    ->scalarNode(self::ACTIVATE_USER)->defaultValue(true)->end()
                                    ->scalarNode(self::AUTO_LOGIN)->defaultValue(true)->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('?send=true')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('community/registration-email.html.twig')->end()
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

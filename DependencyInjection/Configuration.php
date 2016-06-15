<?php

namespace Sulu\Bundle\CommunityBundle\DependencyInjection;

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

    // Type configurations
    const TEMPLATE = 'template';
    const EMBED_TEMPLATE = 'embed_template';
    const FORM_TYPE = 'type';
    const FORM_TYPE_OPTIONS = 'options';
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
                                    // Login configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('community/login.html.twig')->end()
                                    ->scalarNode(self::EMBED_TEMPLATE)->defaultValue('community/login-embed.html.twig')->end()
                                ->end()
                            ->end()
                            // Registration
                            ->arrayNode(self::TYPE_REGISTRATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('community/registration.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(RegistrationType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->addDefaultsIfNotSet()
                                    ->end()
                                    ->scalarNode(self::ACTIVATE_USER)->defaultValue(false)->end()
                                    ->scalarNode(self::AUTO_LOGIN)->defaultValue(true)->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('?send=true')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Registration')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('community/registration-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Confirmation
                            ->arrayNode(self::TYPE_CONFIRMATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Confirmation configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('community/confirmation.html.twig')->end()
                                    ->scalarNode(self::ACTIVATE_USER)->defaultValue(true)->end()
                                    ->scalarNode(self::AUTO_LOGIN)->defaultValue(true)->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue(null)->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Confirmation')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue(null)->end()
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

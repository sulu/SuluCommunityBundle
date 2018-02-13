<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection;

use Sulu\Bundle\CommunityBundle\Form\Type\CompletionType;
use Sulu\Bundle\CommunityBundle\Form\Type\PasswordForgetType;
use Sulu\Bundle\CommunityBundle\Form\Type\PasswordResetType;
use Sulu\Bundle\CommunityBundle\Form\Type\ProfileType;
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
    const EMAIL_FROM_NAME = 'name';
    const EMAIL_FROM_EMAIL = 'email';
    const EMAIL_TO = 'to';
    const EMAIL_TO_NAME = 'name';
    const EMAIL_TO_EMAIL = 'email';
    const ROLE = 'role';
    const WEBSPACE_KEY = 'webspace_key';
    const FIREWALL = 'firewall';
    const MAINTENANCE = 'maintenance';

    // Form types
    const TYPE_LOGIN = 'login';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_COMPLETION = 'completion';
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_PASSWORD_FORGET = 'password_forget';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_BLACKLISTED = 'blacklisted';
    const TYPE_BLACKLIST_CONFIRMED = 'blacklist_confirmed';
    const TYPE_BLACKLIST_DENIED = 'blacklist_denied';
    const TYPE_PROFILE = 'profile';
    const TYPE_EMAIL_CONFIRMATION = 'email_confirmation';

    public static $TYPES = [
        self::TYPE_LOGIN,
        self::TYPE_COMPLETION,
        self::TYPE_CONFIRMATION,
        self::TYPE_REGISTRATION,
        self::TYPE_PASSWORD_FORGET,
        self::TYPE_PASSWORD_RESET,
        self::TYPE_BLACKLISTED,
        self::TYPE_BLACKLIST_CONFIRMED,
        self::TYPE_BLACKLIST_DENIED,
        self::TYPE_PROFILE,
        self::TYPE_EMAIL_CONFIRMATION,
    ];

    // Type configurations
    const ENABLED = 'enabled';
    const TEMPLATE = 'template';
    const SERVICE = 'service';
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
    const DELETE_USER = 'delete_user';

    // Other configurations
    const LAST_LOGIN = 'last_login';
    const REFRESH_INTERVAL = 'refresh_interval';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sulu_community');

        $rootNode
            ->children()
                ->arrayNode(self::LAST_LOGIN)
                    ->canBeEnabled()
                    ->children()
                        ->integerNode(self::REFRESH_INTERVAL)->defaultValue(600)->end()
                    ->end()
                ->end()
                ->arrayNode('webspaces')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            // Basic Webspace Configuration
                            ->arrayNode(self::EMAIL_FROM)
                                ->children()
                                    ->scalarNode(self::EMAIL_FROM_NAME)->defaultValue(null)->end()
                                    ->scalarNode(self::EMAIL_FROM_EMAIL)->defaultValue(null)->end()
                                ->end()
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function($value) {
                                        return [
                                            self::EMAIL_FROM_NAME => $value,
                                            self::EMAIL_FROM_EMAIL => $value,
                                        ];
                                    })
                                ->end()
                            ->end()
                            ->arrayNode(self::EMAIL_TO)
                                ->children()
                                    ->scalarNode(self::EMAIL_TO_NAME)->defaultValue(null)->end()
                                    ->scalarNode(self::EMAIL_TO_EMAIL)->defaultValue(null)->end()
                                ->end()
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function($value) {
                                        return [
                                            self::EMAIL_TO_NAME => $value,
                                            self::EMAIL_TO_EMAIL => $value,
                                        ];
                                    })
                                ->end()
                            ->end()
                            ->scalarNode(self::ROLE)->defaultValue(null)->end()
                            ->scalarNode(self::FIREWALL)->defaultValue(null)->end()
                            // Maintenance
                            ->arrayNode(self::MAINTENANCE)
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Maintenance:maintenance.html.twig')->end()
                                ->end()
                            ->end()
                            // Login
                            ->arrayNode(self::TYPE_LOGIN)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Login:login.html.twig')->end()
                                    ->scalarNode(self::EMBED_TEMPLATE)->defaultValue('SuluCommunityBundle:Login:login-embed.html.twig')->end()
                                ->end()
                            ->end()
                            // Registration
                            ->arrayNode(self::TYPE_REGISTRATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Registration:registration-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(RegistrationType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::ACTIVATE_USER)->defaultValue(false)->end()
                                    ->scalarNode(self::AUTO_LOGIN)->defaultValue(true)->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('?send=true')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Registration')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('SuluCommunityBundle:Registration:registration-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Profile
                            ->arrayNode(self::TYPE_PROFILE)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Profile:profile-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(ProfileType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue(null)->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Email change
                            ->arrayNode(self::TYPE_EMAIL_CONFIRMATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Email change configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:EmailConfirmation:email-confirmation-success.html.twig')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('E-Mail change')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('SuluCommunityBundle:EmailConfirmation:email-confirmation-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Confirmation
                            ->arrayNode(self::TYPE_CONFIRMATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Confirmation configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Confirmation:confirmation-message.html.twig')->end()
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
                            // Blacklisted
                            ->arrayNode(self::TYPE_BLACKLISTED)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Blacklisted configuration
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Blacklisted')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue('SuluCommunityBundle:Blacklist:blacklist-email.html.twig')->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Blacklist denied
                            ->arrayNode(self::TYPE_BLACKLIST_DENIED)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Denied configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Blacklist:blacklist-denied.html.twig')->end()
                                    ->scalarNode(self::DELETE_USER)->defaultTrue()->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Denied')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Blacklist confirmed
                            ->arrayNode(self::TYPE_BLACKLIST_CONFIRMED)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Confirmed configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Blacklist:blacklist-confirmed.html.twig')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Registration')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('SuluCommunityBundle:Registration:registration-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Forget
                            ->arrayNode(self::TYPE_PASSWORD_FORGET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Forget configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Password:forget-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(PasswordForgetType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('?send=true')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Password Forget')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('SuluCommunityBundle:Password:forget-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Forget
                            ->arrayNode(self::TYPE_PASSWORD_RESET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Forget configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Password:reset-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(PasswordResetType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::AUTO_LOGIN)->defaultValue(true)->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('?send=true')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Password Reset')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('SuluCommunityBundle:Password:reset-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Completion
                            ->arrayNode(self::TYPE_COMPLETION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Completion Configuration
                                    ->scalarNode(self::SERVICE)->defaultValue(null)->end()
                                    ->scalarNode(self::TEMPLATE)->defaultValue('SuluCommunityBundle:Completion:completion-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(CompletionType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue('/')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Completion')->end()
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

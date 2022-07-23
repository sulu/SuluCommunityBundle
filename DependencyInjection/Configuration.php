<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\DependencyInjection;

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUser;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistUserRepository;
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
    public const WEBSPACES = 'webspaces';

    // Basic Webspace configuration
    public const EMAIL_FROM = 'from';
    public const EMAIL_FROM_NAME = 'name';
    public const EMAIL_FROM_EMAIL = 'email';
    public const EMAIL_TO = 'to';
    public const EMAIL_TO_NAME = 'name';
    public const EMAIL_TO_EMAIL = 'email';
    public const ROLE = 'role';
    public const WEBSPACE_KEY = 'webspace_key';
    public const FIREWALL = 'firewall';
    public const MAINTENANCE = 'maintenance';

    // Form types
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_COMPLETION = 'completion';
    public const TYPE_CONFIRMATION = 'confirmation';
    public const TYPE_PASSWORD_FORGET = 'password_forget';
    public const TYPE_PASSWORD_RESET = 'password_reset';
    public const TYPE_BLACKLISTED = 'blacklisted';
    public const TYPE_BLACKLIST_CONFIRMED = 'blacklist_confirmed';
    public const TYPE_BLACKLIST_DENIED = 'blacklist_denied';
    public const TYPE_PROFILE = 'profile';
    public const TYPE_EMAIL_CONFIRMATION = 'email_confirmation';

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
    public const ENABLED = 'enabled';
    public const TEMPLATE = 'template';
    public const SERVICE = 'service';
    public const EMBED_TEMPLATE = 'embed_template';
    public const FORM_TYPE = 'type';
    public const FORM_TYPE_OPTIONS = 'options';
    public const ACTIVATE_USER = 'activate_user';
    public const AUTO_LOGIN = 'auto_login';
    public const REDIRECT_TO = 'redirect_to';
    public const EMAIL = 'email';
    public const EMAIL_SUBJECT = 'subject';
    public const EMAIL_ADMIN_TEMPLATE = 'admin_template';
    public const EMAIL_USER_TEMPLATE = 'user_template';
    public const DELETE_USER = 'delete_user';

    // Other configurations
    public const LAST_LOGIN = 'last_login';
    public const REFRESH_INTERVAL = 'refresh_interval';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_community');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('objects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('blacklist_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(BlacklistItem::class)->end()
                                ->scalarNode('repository')->defaultValue(BlacklistItemRepository::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('blacklist_user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(BlacklistUser::class)->end()
                                ->scalarNode('repository')->defaultValue(BlacklistUserRepository::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::LAST_LOGIN)
                    ->canBeEnabled()
                    ->children()
                        ->integerNode(self::REFRESH_INTERVAL)->defaultValue(600)->end()
                    ->end()
                ->end()
                ->arrayNode('webspaces')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('webspaceKey')
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
                                    ->then(function ($value) {
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
                                    ->then(function ($value) {
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
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/maintenance.html.twig')->end()
                                ->end()
                            ->end()
                            // Login
                            ->arrayNode(self::TYPE_LOGIN)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/login.html.twig')->end()
                                    ->scalarNode(self::EMBED_TEMPLATE)->defaultValue('@SuluCommunity/login-embed.html.twig')->end()
                                ->end()
                            ->end()
                            // Registration
                            ->arrayNode(self::TYPE_REGISTRATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/registration-form.html.twig')->end()
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
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('@SuluCommunity/registration-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Profile
                            ->arrayNode(self::TYPE_PROFILE)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/profile-form.html.twig')->end()
                                    ->scalarNode(self::FORM_TYPE)->defaultValue(ProfileType::class)->end()
                                    ->arrayNode(self::FORM_TYPE_OPTIONS)
                                        ->prototype('scalar')->end()->defaultValue([])
                                    ->end()
                                    ->scalarNode(self::REDIRECT_TO)->defaultValue(null)->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Profile change')->end()
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
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/email-confirmation-success.html.twig')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('E-Mail change')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('@SuluCommunity/email-confirmation-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Confirmation
                            ->arrayNode(self::TYPE_CONFIRMATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Confirmation configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/confirmation-message.html.twig')->end()
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
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue('@SuluCommunity/blacklist-email.html.twig')->end()
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
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/blacklist-denied.html.twig')->end()
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
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/blacklist-confirmed.html.twig')->end()
                                    ->arrayNode(self::EMAIL)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::EMAIL_SUBJECT)->defaultValue('Registration')->end()
                                            ->scalarNode(self::EMAIL_ADMIN_TEMPLATE)->defaultValue(null)->end()
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('@SuluCommunity/registration-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Forget
                            ->arrayNode(self::TYPE_PASSWORD_FORGET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Forget configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/password-forget-form.html.twig')->end()
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
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('@SuluCommunity/password-forget-email.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Forget
                            ->arrayNode(self::TYPE_PASSWORD_RESET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Forget configuration
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/password-reset-form.html.twig')->end()
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
                                            ->scalarNode(self::EMAIL_USER_TEMPLATE)->defaultValue('@SuluCommunity/password-reset-email.html.twig')->end()
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
                                    ->scalarNode(self::TEMPLATE)->defaultValue('@SuluCommunity/completion-form.html.twig')->end()
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

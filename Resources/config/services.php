<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Sulu\Bundle\CommunityBundle\Admin\CommunityAdmin;
use Sulu\Bundle\CommunityBundle\Build\CommunityBuilder;
use Sulu\Bundle\CommunityBundle\Command\InitCommand;
use Sulu\Bundle\CommunityBundle\Controller\CompletionController;
use Sulu\Bundle\CommunityBundle\Controller\ConfirmationController;
use Sulu\Bundle\CommunityBundle\Controller\EmailConfirmationController;
use Sulu\Bundle\CommunityBundle\Controller\LoginController;
use Sulu\Bundle\CommunityBundle\Controller\PasswordController;
use Sulu\Bundle\CommunityBundle\Controller\ProfileController;
use Sulu\Bundle\CommunityBundle\Controller\RegistrationController;
use Sulu\Bundle\CommunityBundle\Controller\RegistrationRuleConfirmationController;
use Sulu\Bundle\CommunityBundle\Controller\RegistrationRuleItemController;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationToken;
use Sulu\Bundle\CommunityBundle\Entity\EmailConfirmationTokenRepository;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItemRepository;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUser;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleUserRepository;
use Sulu\Bundle\CommunityBundle\EventListener\CompletionListener;
use Sulu\Bundle\CommunityBundle\EventListener\EmailConfirmationListener;
use Sulu\Bundle\CommunityBundle\EventListener\MailListener;
use Sulu\Bundle\CommunityBundle\EventListener\RegistrationRuleListener;
use Sulu\Bundle\CommunityBundle\Mail\MailFactory;
use Sulu\Bundle\CommunityBundle\Mail\MailFactoryInterface;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManager;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistry;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistryInterface;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManager;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManagerInterface;
use Sulu\Bundle\CommunityBundle\Manager\UserManager;
use Sulu\Bundle\CommunityBundle\Manager\UserManagerInterface;
use Sulu\Component\Media\SystemCollections\SystemCollectionManagerInterface;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    // Commands
    $services->set('sulu_community.command.init', InitCommand::class)
        ->args([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_core.webspace.webspace_manager'),
            new Reference('sulu_community.community_manager.registry'),
            new Reference('sulu_admin.admin_pool'),
        ])
        ->tag('sulu.context', ['context' => 'admin'])
        ->tag('console.command');

    // Community Manager
    $services->set('sulu_community.community_manager.registry', CommunityManagerRegistry::class)
        ->public()
        ->args([[]]);
    $services->alias(CommunityManagerRegistryInterface::class, 'sulu_community.community_manager.registry');

    // The first two arguments are placeholders, CommunityManagerCompilerPass replaces them
    // for each webspace.
    $services->set('sulu_community.community_manager', CommunityManager::class)
        ->abstract()
        ->args([
            null,
            null,
            new Reference('event_dispatcher'),
            new Reference('security.token_storage'),
            new Reference('sulu_community.user_manager'),
            new Reference('sulu_community.mail_factory'),
        ]);

    // User Manager
    $services->set('sulu_community.user_manager', UserManager::class)
        ->public()
        ->args([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_core.webspace.webspace_manager'),
            new Reference('sulu_security.token_generator'),
            new Reference('sulu.repository.user'),
            new Reference('sulu.repository.role'),
            new Reference('sulu.repository.contact'),
            new Reference('sulu_contact.contact_manager'),
        ]);
    $services->alias(UserManagerInterface::class, 'sulu_community.user_manager');

    // mail-listener
    $services->set('sulu_community.mail_listener', MailListener::class)
        ->args([
            new Reference('sulu_community.mail_factory'),
        ])
        ->tag('kernel.event_subscriber');

    // registration-rule-listener
    $services->set('sulu_community.registration_rule_listener', RegistrationRuleListener::class)
        ->args([
            new Reference('sulu.repository.registration_rule_item'),
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_security.token_generator'),
            new Reference('sulu_community.mail_factory'),
        ])
        ->tag('kernel.event_subscriber');

    // completion-listener
    $services->set('sulu_community.completion_listener', CompletionListener::class)
        ->args([
            new Reference('sulu_core.webspace.request_analyzer'),
            new Reference('router'),
            new Reference('security.token_storage'),
            '%fragment.path%',
            [],
            '%sulu_community.webspaces_config%',
        ])
        ->tag('kernel.event_subscriber')
        ->tag('sulu.context', ['context' => 'website']);

    // sulu-admin
    $services->set('sulu_community.admin', CommunityAdmin::class)
        ->args([
            new Reference('sulu_security.security_checker'),
            new Reference('sulu_core.webspace.webspace_manager'),
            new Reference('sulu_admin.view_builder_factory'),
            '%sulu_community.webspaces_config%',
        ])
        ->tag('sulu.admin')
        ->tag('sulu.context', ['context' => 'admin']);

    // builder
    $services->set('sulu_community.build', CommunityBuilder::class)
        ->tag('massive_build.builder');

    // registration_rule
    $services->set('sulu_community.registration_rule.item_manager', RegistrationRuleItemManager::class)
        ->public()
        ->args([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu.repository.registration_rule_item'),
        ]);
    $services->alias(RegistrationRuleItemManagerInterface::class, 'sulu_community.registration_rule.item_manager');

    $services->set('sulu_community.registration_rule.item_repository', RegistrationRuleItemRepository::class)
        ->public()
        ->factory([new Reference('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([
            RegistrationRuleItem::class,
        ]);
    $services->alias(RegistrationRuleItemRepository::class, 'sulu_community.registration_rule.item_repository');

    $services->set('sulu_community.registration_rule.user_repository', RegistrationRuleUserRepository::class)
        ->public()
        ->factory([new Reference('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([
            RegistrationRuleUser::class,
        ]);
    $services->alias(RegistrationRuleUserRepository::class, 'sulu_community.registration_rule.user_repository');

    // email-confirmation
    $services->set('sulu_community.email_confirmation.listener', EmailConfirmationListener::class)
        ->args([
            new Reference('sulu_community.mail_factory'),
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_community.email_confirmation.repository'),
            new Reference('sulu_security.token_generator'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set('sulu_community.email_confirmation.repository', EmailConfirmationTokenRepository::class)
        ->public()
        ->factory([new Reference('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([
            EmailConfirmationToken::class,
        ]);
    $services->alias(EmailConfirmationTokenRepository::class, 'sulu_community.email_confirmation.repository');

    // mailing
    $services->set('sulu_community.mail_factory', MailFactory::class)
        ->args([
            new Reference('mailer'),
            new Reference('twig'),
            new Reference('translator'),
        ]);
    $services->alias(MailFactoryInterface::class, 'sulu_community.mail_factory');

    // Controller
    $services->set('sulu_community.controller.registration_rule_item', RegistrationRuleItemController::class)
        ->public()
        ->args([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_core.doctrine_rest_helper'),
            new Reference('sulu_core.doctrine_list_builder_factory'),
            new Reference('sulu_core.list_builder.field_descriptor_factory'),
            new Reference('sulu_community.registration_rule.item_manager'),
            new Reference('fos_rest.view_handler.default'),
            new Reference('security.token_storage'),
        ]);

    // Website Controllers
    foreach ([
        ProfileController::class,
        RegistrationRuleConfirmationController::class,
        CompletionController::class,
        ConfirmationController::class,
        EmailConfirmationController::class,
        LoginController::class,
        PasswordController::class,
        RegistrationController::class,
    ] as $websiteController) {
        $services->set($websiteController, $websiteController)
            ->public()
            ->tag('container.service_subscriber')
            ->tag('controller.service_arguments')
            ->tag('sulu.context', ['context' => 'website'])
            ->call('setContainer', [
                new Reference(PsrContainerInterface::class),
            ]);
    }

    // Should be in sulu/sulu (see https://github.com/sulu/sulu/pull/5195)
    $services->alias(SystemCollectionManagerInterface::class, 'sulu_media.system_collections.manager');
};

<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Security;

/**
 * Integrates community into sulu-admin.
 */
class CommunityAdmin extends Admin
{
    public const BLACKLIST_ITEM_SECURITY_CONTEXT = 'sulu.community.blacklist_items';
    public const BLACKLIST_ITEM_LIST_VIEW = 'sulu_community.blacklist_item';
    public const BLACKLIST_ITEM_ADD_FORM_VIEW = 'sulu_community.blacklist_item.add_form';
    public const BLACKLIST_ITEM_EDIT_FORM_VIEW = 'sulu_community.blacklist_item.edit_form';

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @var WebspaceManagerInterface
     */
    private $webspaceManager;

    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    /**
     * @var mixed[]
     */
    private $webspacesConfiguration;

    /**
     * @param mixed[] $webspacesConfiguration
     */
    public function __construct(
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ViewBuilderFactoryInterface $viewBuilderFactory,
        array $webspacesConfiguration
    ) {
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->webspacesConfiguration = $webspacesConfiguration;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $tags = new NavigationItem('sulu_community.blacklist');
            $tags->setPosition(40);
            $tags->setView(static::BLACKLIST_ITEM_LIST_VIEW);

            $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($tags);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $formToolbarActions = [];
        $listToolbarActions = [];

        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(static::BLACKLIST_ITEM_SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::BLACKLIST_ITEM_LIST_VIEW, '/blacklist')
                    ->setResourceKey('blacklist_items')
                    ->setListKey('blacklist_items')
                    ->setTitle('sulu_community.blacklist')
                    ->addListAdapters(['table'])
                    ->setAddView(static::BLACKLIST_ITEM_ADD_FORM_VIEW)
                    ->setEditView(static::BLACKLIST_ITEM_EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::BLACKLIST_ITEM_ADD_FORM_VIEW, '/blacklist/add')
                    ->setResourceKey('blacklist_items')
                    ->setBackView(static::BLACKLIST_ITEM_LIST_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::BLACKLIST_ITEM_ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey('blacklist_items')
                    ->setFormKey('blacklist_item_details')
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::BLACKLIST_ITEM_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::BLACKLIST_ITEM_ADD_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::BLACKLIST_ITEM_EDIT_FORM_VIEW, '/blacklist/:id')
                    ->setResourceKey('blacklist_items')
                    ->setBackView(static::BLACKLIST_ITEM_LIST_VIEW)
                    ->setTitleProperty('name')
            );
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::BLACKLIST_ITEM_EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey('blacklist_items')
                    ->setFormKey('blacklist_item_details')
                    ->setTabTitle('sulu_admin.details')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::BLACKLIST_ITEM_EDIT_FORM_VIEW)
            );
        }
    }

    public function getSecurityContexts()
    {
        $systems = [];

        $webspaceCollection = $this->webspaceManager->getWebspaceCollection();

        $webspaceKeys = \array_keys($webspaceCollection->getWebspaces());

        foreach ($this->webspacesConfiguration as $webspaceKey => $webspaceConfig) {
            $webspace = $webspaceCollection->getWebspace($webspaceKey);

            if (!$webspace) {
                throw new \InvalidArgumentException(\sprintf('Webspace "%s" not found for "sulu_community" expected one of %s.', $webspaceKey, '"' . \implode('", "', $webspaceKeys) . '"'));
            }

            /** @var Security|null $security */
            $security = $webspace->getSecurity();

            if (!$security) {
                throw new \InvalidArgumentException(\sprintf('Missing "<security><system>Website</system><security>" configuration in webspace "%s" for "sulu_community".', $webspaceKey));
            }

            $system = $security->getSystem();
            $systems[$system] = [];
        }

        return \array_merge(
            $systems,
            [
                'Sulu' => [
                    'Settings' => [
                        self::BLACKLIST_ITEM_SECURITY_CONTEXT => [
                            PermissionTypes::VIEW,
                            PermissionTypes::ADD,
                            PermissionTypes::EDIT,
                            PermissionTypes::DELETE,
                        ],
                    ],
                ],
            ]
        );
    }
}

<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

/**
 * Integrates community into sulu-admin.
 */
class CommunityAdmin extends Admin
{
    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @var WebspaceManagerInterface
     */
    private $webspaceManager;

    /**
     * @var array
     */
    private $webspacesConfiguration;

    /**
     * @param SecurityCheckerInterface $securityChecker
     * @param WebspaceManagerInterface $webspaceManager
     * @param array $webspacesConfiguration
     * @param string $title
     */
    public function __construct(
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        array $webspacesConfiguration,
        $title
    ) {
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->webspacesConfiguration = $webspacesConfiguration;

        $rootNavigationItem = new NavigationItem($title);
        $section = new NavigationItem('navigation.modules');
        $section->setPosition(20);

        $settings = new NavigationItem('navigation.settings');
        $settings->setPosition(40);
        $settings->setIcon('settings');

        if ($this->securityChecker->hasPermission('sulu.community.blacklist', 'view')) {
            $roles = new NavigationItem('navigation.settings.blacklist', $settings);
            $roles->setPosition(30);
            $roles->setAction('settings/blacklist');
            $roles->setIcon('ban');
        }

        if ($settings->hasChildren()) {
            $section->addChild($settings);
            $rootNavigationItem->addChild($section);
        }

        $this->setNavigation(new Navigation($rootNavigationItem));
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContexts()
    {
        $systems = [];

        $webspaceCollection = $this->webspaceManager->getWebspaceCollection();

        $webspaceKeys = array_keys($webspaceCollection->getWebspaces());

        foreach ($this->webspacesConfiguration as $webspaceKey => $webspaceConfig) {
            $webspace = $webspaceCollection->getWebspace($webspaceKey);

            if (!$webspace) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Webspace "%s" not found for "sulu_community" expected one of %s.',
                        $webspaceKey,
                        '"' . implode('", "', $webspaceKeys) . '"'
                    )
                );
            }

            $security = $webspace->getSecurity();

            if (!$security) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Missing "<security><system>Website</system><security>" configuration in webspace "%s" for "sulu_community".',
                        $webspaceKey
                    )
                );
            }

            $system = $security->getSystem();
            $systems[$system] = [];
        }

        return array_merge(
            $systems,
            [
                'Sulu' => [
                    'Settings' => [
                        'sulu.community.blacklist' => [
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

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'sulucommunity';
    }
}

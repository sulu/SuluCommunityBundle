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
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
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
     * @param mixed[] $webspacesConfiguration
     */
    public function __construct(
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        array $webspacesConfiguration
    ) {
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->webspacesConfiguration = $webspacesConfiguration;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        // TODO implement Blacklisting navigation items
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        // TODO implement Blacklisting views
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

            /** @var Security|null $security */
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
}

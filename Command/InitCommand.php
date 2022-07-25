<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\AdminPool;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistryInterface;
use Sulu\Bundle\SecurityBundle\Entity\Permission;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Component\Security\Authentication\RoleInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Security;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create the user roles for the community.
 */
class InitCommand extends Command
{
    public const NAME = 'sulu:community:init';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WebspaceManagerInterface
     */
    private $webspaceManager;

    /**
     * @var CommunityManagerRegistryInterface
     */
    private $communityManagerRegistry;

    /**
     * @var AdminPool
     */
    private $adminPool;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        CommunityManagerRegistryInterface $communityManagerRegistry,
        AdminPool $adminPool
    ) {
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->communityManagerRegistry = $communityManagerRegistry;
        $this->adminPool = $adminPool;
        parent::__construct(self::NAME);
    }

    public function configure(): void
    {
        $this->setDescription('Create the user roles for the community.')
            ->addArgument('webspace', null, 'A specific webspace key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string|null $webspaceKey */
        $webspaceKey = $input->getArgument('webspace');

        if (null !== $webspaceKey) {
            $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);

            if (!$webspace) {
                throw new \InvalidArgumentException(\sprintf('Given webspace "%s" is invalid', $webspaceKey));
            }

            $this->initWebspace($webspace, $output);
            $this->entityManager->flush();

            return 0;
        }

        /** @var Webspace $webspace */
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            $this->initWebspace($webspace, $output);
            $this->entityManager->flush();
        }

        return 0;
    }

    /**
     * Create role for specific webspace.
     *
     * @throws \Exception
     */
    private function initWebspace(Webspace $webspace, OutputInterface $output): void
    {
        $webspaceKey = $webspace->getKey();

        /** @var Security|null $security */
        $security = $webspace->getSecurity();

        if (!$security || !$this->communityManagerRegistry->has($webspaceKey)) {
            return;
        }

        $communityManager = $this->communityManagerRegistry->get($webspaceKey);
        /** @var string $roleName */
        $roleName = $communityManager->getConfigProperty(Configuration::ROLE);
        $system = $security->getSystem();

        // Create role if not exists
        $output->writeln(
            \sprintf(
                $this->createRoleIfNotExists($roleName, $system, $webspaceKey),
                $roleName,
                $system
            )
        );
    }

    /**
     * Create a role for a specific system if not exists.
     */
    private function createRoleIfNotExists(string $roleName, string $system, string $webspaceKey): string
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->entityManager->getRepository(RoleInterface::class);

        if (\method_exists(Role::class, 'setKey')) {
            /** @var RoleInterface|null $role */
            $role = $roleRepository->findOneBy(['key' => $roleName, 'system' => $system]);
        } else {
            // can be removed when min requirement sulu 2.1
            /** @var RoleInterface|null $role */
            $role = $roleRepository->findOneBy(['name' => $roleName, 'system' => $system]);
        }

        if ($role) {
            if ($this->addPermissions($role, $system, $webspaceKey)) {
                return 'Role "%s" for system "%s" was updated with new permissions.';
            }

            return 'Role "%s" for system "%s" already exists.';
        }

        // Create Role
        $outputMessage = 'Create role <info>"%s"</info> for system <info>"%s"</info>';

        /** @var Role $role */
        $role = $roleRepository->createNew();
        $role->setSystem($system);
        $role->setName($roleName);
        if (\method_exists(Role::class, 'setKey')) {
            // can be removed when min requirement sulu 2.1
            $role->setKey($roleName);
        }

        $this->addPermissions($role, $system, $webspaceKey);

        $this->entityManager->persist($role);

        return $outputMessage;
    }

    private function addPermissions(RoleInterface $role, string $system, string $webspaceKey): bool
    {
        $securityContexts = $this->adminPool->getSecurityContexts();
        $securityContextsFlat = [];
        foreach ($securityContexts[$system] as $section => $contexts) {
            foreach ($contexts as $context => $permissionTypes) {
                if (\is_array($permissionTypes)) {
                    $securityContextsFlat[] = $context;
                } else {
                    // FIXME here for BC reasons, because the array used to only contain values without permission types
                    $securityContextsFlat[] = $permissionTypes;
                }
            }
        }

        $webspaceSecurityContext = 'sulu.webspaces.' . $webspaceKey;
        $permissionsAdded = false;
        foreach ($securityContextsFlat as $securityContext) {
            foreach ($role->getPermissions() as $permission) {
                if ($permission->getContext() === $securityContext) {
                    continue 2;
                }
            }

            if (0 === \strpos($securityContext, 'sulu.webspaces.')
                && $webspaceSecurityContext !== $securityContext
            ) {
                // Do not add permissions for other webspaces
                continue;
            }

            $permission = new Permission();
            $permission->setRole($role);
            $permission->setContext($securityContext);
            $permission->setPermissions(127);
            $role->addPermission($permission);

            $this->entityManager->persist($permission);

            $permissionsAdded = true;
        }

        return $permissionsAdded;
    }
}

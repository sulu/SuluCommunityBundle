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
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerRegistryInterface;
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
    const NAME = 'sulu:community:init';

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

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        CommunityManagerRegistryInterface $communityManagerRegistry
    ) {
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->communityManagerRegistry = $communityManagerRegistry;
        parent::__construct(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setDescription('Create the user roles for the community.')
            ->addArgument('webspace', null, 'A specific webspace key.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $webspaceKey */
        $webspaceKey = $input->getArgument('webspace');

        if (null !== $webspaceKey) {
            $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);

            if (!$webspace) {
                throw new \InvalidArgumentException(sprintf('Given webspace "%s" is invalid', $webspaceKey));
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
    }

    /**
     * Create role for specific webspace.
     *
     * @throws \Exception
     */
    protected function initWebspace(Webspace $webspace, OutputInterface $output): void
    {
        $webspaceKey = $webspace->getKey();

        /** @var Security|null $security */
        $security = $webspace->getSecurity();

        if (!$security || !$this->communityManagerRegistry->has($webspaceKey)) {
            return;
        }

        $communityManager = $this->communityManagerRegistry->get($webspaceKey);
        $roleName = $communityManager->getConfigProperty(Configuration::ROLE);
        $system = $security->getSystem();

        // Create role if not exists
        $output->writeln(
            sprintf(
                $this->createRoleIfNotExists($roleName, $system),
                $roleName,
                $system
            )
        );
    }

    /**
     * Create a role for a specific system if not exists.
     */
    protected function createRoleIfNotExists(string $roleName, string $system): string
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->entityManager->getRepository(RoleInterface::class);

        $role = $roleRepository->findOneBy(['name' => $roleName, 'system' => $system]);

        $outputMessage = 'Role "%s" for system "%s" already exists.';

        // Create Role
        if ($role) {
            return $outputMessage;
        }

        $outputMessage = 'Create role <info>"%s"</info> for system <info>"%s"</info>';

        /** @var Role $role */
        $role = $roleRepository->createNew();
        $role->setSystem($system);
        $role->setName($roleName);

        $this->entityManager->persist($role);

        return $outputMessage;
    }
}

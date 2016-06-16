<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManager;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\RoleRepository;
use Sulu\Component\Webspace\Webspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sulu:community:init')
            ->setDescription('Create the user roles for the community.')
            ->addArgument('webspace', null, 'A specific webspace key.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $webspaceManager = $this->getContainer()->get('sulu_core.webspace.webspace_manager');

        $webspaceKey = $input->getArgument('webspace');

        if (!$webspaceKey) {
            /** @var Webspace $webspace */
            foreach ($webspaceManager->getWebspaceCollection() as $webspace) {
                $this->initWebspace($webspace, $output);
            }
        } else {
            $this->initWebspace($webspaceManager->findWebspaceByKey($webspaceKey), $output);
        }
    }

    /**
     * @param Webspace $webspace
     *
     * @throws \Exception
     */
    protected function initWebspace($webspace, OutputInterface $output)
    {
        $webspaceKey = $webspace->getKey();

        $communityServiceName = sprintf('sulu_community.%s.community_manager', $webspaceKey);

        if ($webspace->getSecurity() && $this->getContainer()->has($communityServiceName)) {
            /** @var CommunityManager $communityManager */
            $communityManager = $this->getContainer()->get($communityServiceName);
            $roleName = $communityManager->getConfigProperty(Configuration::ROLE);
            $system = $webspace->getSecurity()->getSystem();

            // Create role if not exists
            $output->writeln(
                sprintf(
                    $this->createRoleIfNotExists($roleName, $system),
                    $roleName,
                    $system
                )
            );
        }
    }

    /**
     * @param $roleName
     * @param $system
     *
     * @return string
     */
    protected function createRoleIfNotExists($roleName, $system)
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getContainer()->get('sulu.repository.role');

        $role = $roleRepository->findOneBy(['name' => $roleName, 'system' => $system]);

        $outputMessage = 'Role "%s" for system "%s" already exists.';

        // Create Role
        if (!$role) {
            $outputMessage = 'Create role <info>"%s"</info> for system <info>"%s"</info>';

            /** @var Role $role */
            $role = $roleRepository->createNew();
            $role->setSystem($system);
            $role->setName($roleName);

            /** @var EntityManagerInterface $entityManager */
            $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
            $entityManager->persist($role);
            $entityManager->flush();
        }

        return $outputMessage;
    }
}

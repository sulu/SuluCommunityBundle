<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides admin-api for blacklist-items.
 *
 * @NamePrefix("sulu_community.")
 * @RouteResource("blacklist-item")
 */
class BlacklistItemController extends RestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * Returns fields.
     *
     * @return Response
     */
    public function fieldsAction()
    {
        return $this->handleView($this->view(array_values($this->getFieldDescriptors()), 200));
    }

    /**
     * Return a list of items.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $restHelper = $this->get('sulu_core.doctrine_rest_helper');
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');

        $fieldDescriptors = $this->getFieldDescriptors();
        $listBuilder = $factory->create(BlacklistItem::class);
        $restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listResponse = $this->prepareListResponse($request, $listBuilder, $fieldDescriptors);

        return $this->handleView(
            $this->view(
                new ListRepresentation(
                    $listResponse,
                    self::$entityKey,
                    'sulu_community.get_blacklist-items',
                    $request->query->all(),
                    $listBuilder->getCurrentPage(),
                    $listBuilder->getLimit(),
                    $listBuilder->count()
                )
            )
        );
    }

    /**
     * Returns a single item.
     *
     * @param int $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $manager = $this->get('sulu_community.blacklisting.item_manager');

        return $this->handleView($this->view($manager->find($id)));
    }

    /**
     * Creates a new item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $manager = $this->get('sulu_community.blacklisting.item_manager');
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $item = $manager->create()
            ->setPattern($this->getRequestParameter($request, 'pattern', true))
            ->setType($this->getRequestParameter($request, 'type', true));

        $entityManager->flush();

        return $this->handleView($this->view($item));
    }

    /**
     * Deletes given item.
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $manager = $this->get('sulu_community.blacklisting.item_manager');

        $manager->delete($id);
        $entityManager->flush();

        return $this->handleView($this->view(null));
    }

    /**
     * Deletes a list of items.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $manager = $this->get('sulu_community.blacklisting.item_manager');

        $manager->delete(array_filter(explode(',', $request->get('ids', ''))));
        $entityManager->flush();

        return $this->handleView($this->view(null));
    }

    /**
     * Updates given item.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function putAction($id, Request $request)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $manager = $this->get('sulu_community.blacklisting.item_manager');

        $item = $manager->find($id)
            ->setPattern($this->getRequestParameter($request, 'pattern', true))
            ->setType($this->getRequestParameter($request, 'type', true));

        $entityManager->flush();

        return $this->handleView($this->view($item));
    }

    /**
     * Creates the field-descriptors for blacklist-items.
     *
     * @return array
     */
    private function getFieldDescriptors()
    {
        return [
            'id' => new DoctrineFieldDescriptor('id', 'id', BlacklistItem::class, 'public.id', [], true),
            'pattern' => new DoctrineFieldDescriptor(
                'pattern',
                'pattern',
                BlacklistItem::class,
                'community.blacklist.pattern',
                [],
                false,
                false,
                'string',
                '',
                '',
                true,
                true
            ),
            'type' => new DoctrineFieldDescriptor(
                'type',
                'type',
                BlacklistItem::class,
                'public.type',
                [],
                false,
                false,
                'select',
                '',
                '',
                true,
                true
            ),
        ];
    }

    /**
     * Prepare list response.
     *
     * @param Request $request
     * @param DoctrineListBuilder $listBuilder
     * @param array $fieldDescriptors
     *
     * @return array
     */
    private function prepareListResponse(Request $request, DoctrineListBuilder $listBuilder, array $fieldDescriptors)
    {
        $idsParameter = $request->get('ids');
        $ids = array_filter(explode(',', $idsParameter));
        if (null !== $idsParameter && 0 === count($ids)) {
            return [];
        }

        if (null !== $idsParameter) {
            $listBuilder->in($fieldDescriptors['id'], $ids);
        }

        return $listBuilder->execute();
    }
}

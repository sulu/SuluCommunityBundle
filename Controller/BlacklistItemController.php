<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Manager\BlacklistItemManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListBuilderInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides admin-api for blacklist-items.
 *
 * @NamePrefix("sulu_community.")
 * @RouteResource("blacklist-item")
 */
class BlacklistItemController extends AbstractRestController implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RestHelperInterface
     */
    protected $restHelper;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    protected $listBuilderFactory;

    /**
     * @var BlacklistItemManagerInterface
     */
    protected $blacklistItemManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactoryInterface $listBuilderFactory,
        BlacklistItemManagerInterface $blacklistItemManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->entityManager = $entityManager;
        $this->restHelper = $restHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->blacklistItemManager = $blacklistItemManager;

        parent::__construct($viewHandler, $tokenStorage);
    }

    /**
     * Returns fields.
     */
    public function fieldsAction(): Response
    {
        return $this->handleView($this->view(\array_values($this->getFieldDescriptors()), 200));
    }

    /**
     * Return a list of items.
     */
    public function cgetAction(Request $request): Response
    {
        $fieldDescriptors = $this->getFieldDescriptors();
        $listBuilder = $this->listBuilderFactory->create(BlacklistItem::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listResponse = $this->prepareListResponse($request, $listBuilder, $fieldDescriptors);

        return $this->handleView(
            $this->view(
                new ListRepresentation(
                    $listResponse,
                    'blacklist_items',
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
     */
    public function getAction(int $id): Response
    {
        return $this->handleView($this->view($this->blacklistItemManager->find($id)));
    }

    /**
     * Creates a new item.
     */
    public function postAction(Request $request): Response
    {
        $item = $this->blacklistItemManager->create()
            ->setPattern($this->getRequestParameter($request, 'pattern', true))
            ->setType($this->getRequestParameter($request, 'type', true));

        $this->entityManager->flush();

        return $this->handleView($this->view($item));
    }

    /**
     * Deletes given item.
     */
    public function deleteAction(int $id): Response
    {
        $this->blacklistItemManager->delete($id);
        $this->entityManager->flush();

        return $this->handleView($this->view(null));
    }

    /**
     * Deletes a list of items.
     */
    public function cdeleteAction(Request $request): Response
    {
        $ids = \array_map(function ($id) {
            return (int) $id;
        }, \array_filter(\explode(',', (string) $request->query->get('ids', ''))));

        $this->blacklistItemManager->delete($ids);
        $this->entityManager->flush();

        return $this->handleView($this->view(null));
    }

    /**
     * Updates given item.
     */
    public function putAction(int $id, Request $request): Response
    {
        $item = $this->blacklistItemManager->find($id)
            ->setPattern($this->getRequestParameter($request, 'pattern', true))
            ->setType($this->getRequestParameter($request, 'type', true));

        $this->entityManager->flush();

        return $this->handleView($this->view($item));
    }

    /**
     * Creates the field-descriptors for blacklist-items.
     *
     * @return DoctrineFieldDescriptor[]
     */
    private function getFieldDescriptors(): array
    {
        return [
            'id' => new DoctrineFieldDescriptor(
                'id',
                'id',
                BlacklistItem::class,
                'public.id',
                [],
                FieldDescriptorInterface::VISIBILITY_NO
            ),
            'pattern' => new DoctrineFieldDescriptor(
                'pattern',
                'pattern',
                BlacklistItem::class,
                'community.blacklist.pattern',
                [],
                FieldDescriptorInterface::VISIBILITY_ALWAYS,
                FieldDescriptorInterface::SEARCHABILITY_YES,
                'string',
                true
            ),
            'type' => new DoctrineFieldDescriptor(
                'type',
                'type',
                BlacklistItem::class,
                'public.type',
                [],
                FieldDescriptorInterface::VISIBILITY_ALWAYS,
                FieldDescriptorInterface::SEARCHABILITY_YES,
                'select',
                true
            ),
        ];
    }

    /**
     * Prepare list response.
     *
     * @param DoctrineFieldDescriptor[] $fieldDescriptors
     *
     * @return array|mixed
     */
    private function prepareListResponse(Request $request, ListBuilderInterface $listBuilder, array $fieldDescriptors)
    {
        /** @var string $idsParameter */
        $idsParameter = $request->get('ids');
        $ids = \array_filter(\explode(',', $idsParameter));
        if (null !== $idsParameter && 0 === \count($ids)) {
            return [];
        }

        if (null !== $idsParameter) {
            $listBuilder->in($fieldDescriptors['id'], $ids);
        }

        return $listBuilder->execute();
    }
}

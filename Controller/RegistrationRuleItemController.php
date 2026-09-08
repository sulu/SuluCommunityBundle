<?php

declare(strict_types=1);

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
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\CommunityBundle\Admin\CommunityAdmin;
use Sulu\Bundle\CommunityBundle\Entity\RegistrationRuleItem;
use Sulu\Bundle\CommunityBundle\Manager\RegistrationRuleItemManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\MissingParameterException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\ListBuilderInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides admin-api for registration-rule-items.
 */
class RegistrationRuleItemController extends AbstractRestController implements SecuredControllerInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected RestHelperInterface $restHelper,
        protected DoctrineListBuilderFactoryInterface $listBuilderFactory,
        protected FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        protected RegistrationRuleItemManagerInterface $registrationRuleItemManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function fieldsAction(): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('registration_rule_items') ?? [];

        return $this->handleView($this->view(\array_values($fieldDescriptors), 200));
    }

    public function cgetAction(Request $request): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('registration_rule_items') ?? [];
        $listBuilder = $this->listBuilderFactory->create(RegistrationRuleItem::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listResponse = $this->prepareListResponse($request, $listBuilder, $fieldDescriptors);

        return $this->handleView(
            $this->view(
                new PaginatedRepresentation(
                    $listResponse,
                    'registration_rule_items',
                    (int) $listBuilder->getCurrentPage(),
                    (int) $listBuilder->getLimit(),
                    $listBuilder->count(),
                )
            )
        );
    }

    public function getAction(int $id): Response
    {
        return $this->handleView($this->view($this->registrationRuleItemManager->find($id)));
    }

    public function postAction(Request $request): Response
    {
        $pattern = $request->request->get('pattern') ?? throw new MissingParameterException(self::class, 'pattern');
        $type = $request->request->get('type') ?? throw new MissingParameterException(self::class, 'type');

        $item = $this->registrationRuleItemManager->create()
            ->setPattern((string) $pattern)
            ->setType((string) $type);

        $this->entityManager->flush();

        return $this->handleView($this->view($item));
    }

    public function deleteAction(int $id): Response
    {
        $this->registrationRuleItemManager->delete($id);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    public function cdeleteAction(Request $request): Response
    {
        $ids = \array_map(
            static fn ($id): int => (int) $id,
            \array_filter(\explode(',', (string) $request->query->get('ids', ''))),
        );

        $this->registrationRuleItemManager->delete($ids);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    public function putAction(int $id, Request $request): Response
    {
        $item = $this->registrationRuleItemManager->find($id);
        if (null === $item) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        $pattern = $request->request->get('pattern') ?? throw new MissingParameterException(self::class, 'pattern');
        $type = $request->request->get('type') ?? throw new MissingParameterException(self::class, 'type');

        $item->setPattern((string) $pattern)
            ->setType((string) $type);

        $this->entityManager->flush();

        return $this->handleView($this->view($item));
    }

    /**
     * @param array<\Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface> $fieldDescriptors
     *
     * @return mixed[]
     */
    private function prepareListResponse(Request $request, ListBuilderInterface $listBuilder, array $fieldDescriptors): array
    {
        /** @var string|null $idsParameter */
        $idsParameter = $request->query->get('ids');
        if (null === $idsParameter) {
            return $listBuilder->execute();
        }

        $ids = \array_filter(\explode(',', $idsParameter));
        if (0 === \count($ids)) {
            return [];
        }

        $listBuilder->in($fieldDescriptors['id'], $ids);

        $sorted = [];
        foreach ($listBuilder->execute() as $item) {
            $position = \array_search((string) $item['id'], $ids, true);
            if (false !== $position) {
                $sorted[$position] = $item;
            }
        }

        \ksort($sorted);

        return \array_values($sorted);
    }

    public function getSecurityContext(): string
    {
        return CommunityAdmin::REGISTRATION_RULE_ITEM_SECURITY_CONTEXT;
    }
}

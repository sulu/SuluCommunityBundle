<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\Validator\Constraints;

use Sulu\Bundle\CommunityBundle\Entity\BlacklistItem;
use Sulu\Bundle\CommunityBundle\Entity\BlacklistItemRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * A validator to check if the given email is blocked via blacklist.
 */
class BlockedValidator extends ConstraintValidator
{
    /**
     * @var BlacklistItemRepository
     */
    protected $blacklistItemRepository;

    public function __construct(BlacklistItemRepository $blacklistItemRepository)
    {
        $this->blacklistItemRepository = $blacklistItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Blocked) {
            return;
        }

        $items = $this->blacklistItemRepository->findBySender($value);

        foreach ($items as $item) {
            if (BlacklistItem::TYPE_BLOCK === $item->getType()) {
                return $this->context->addViolation($constraint->message, ['%email%' => $value]);
            }
        }
    }
}

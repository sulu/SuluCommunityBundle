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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

if (version_compare(Kernel::VERSION, '4.0.0', '<')) {
    /**
     * @internal
     */
    trait RenderCompatibilityTrait
    {
        /**
         * {@inheritdoc}
         */
        public function render($view, array $parameters = [], Response $response = null)
        {
            return parent::render(
                $view,
                $this->getTemplateAttributes($parameters),
                $response
            );
        }

        /**
         * {@inheritdoc}
         */
        public function renderView($view, array $parameters = [])
        {
            return parent::renderView($view, $this->getTemplateAttributes($parameters));
        }

        /**
         * Set Sulu template attributes.
         *
         * @param array $custom
         *
         * @return array
         */
        private function getTemplateAttributes($custom = [])
        {
            return $this->get('sulu_website.resolver.template_attribute')->resolve($custom);
        }
    }
} else {
    /**
     * @internal
     */
    trait RenderCompatibilityTrait
    {
        /**
         * {@inheritdoc}
         */
        public function render(string $view, array $parameters = [], Response $response = null): Response
        {
            return parent::render(
                $view,
                $this->getTemplateAttributes($parameters),
                $response
            );
        }

        /**
         * {@inheritdoc}
         */
        public function renderView(string $view, array $parameters = []): string
        {
            return parent::renderView($view, $this->getTemplateAttributes($parameters));
        }
    }
}

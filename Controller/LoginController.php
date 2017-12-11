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

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Component\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Login and login embed page.
 */
class LoginController extends AbstractController
{
    const TYPE = Configuration::TYPE_LOGIN;

    /**
     * Show Login page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->renderTemplate(Configuration::TYPE_LOGIN, [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * ESI Action to show user on every page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function embedAction(Request $request)
    {
        $communityManager = $this->getCommunityManager($this->getWebspaceKey());

        $maintenance = $communityManager->getConfigTypeProperty(Configuration::MAINTENANCE, Configuration::ENABLED);

        $response = $this->render(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::EMBED_TEMPLATE),
            [
                'maintenanceMode' => $maintenance,
            ]
        );

        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->set(HttpCache::HEADER_REVERSE_PROXY_TTL, 0);

        return $response;
    }
}

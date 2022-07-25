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

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\HttpCacheBundle\Cache\SuluHttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Handles Login and login embed page.
 */
class LoginController extends AbstractController
{
    public const TYPE = Configuration::TYPE_LOGIN;

    /**
     * Show Login page.
     */
    public function indexAction(Request $request): Response
    {
        $authenticationUtils = $this->getAuthenticationUtils();

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
     */
    public function embedAction(Request $request): Response
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
        $response->headers->set(SuluHttpCache::HEADER_REVERSE_PROXY_TTL, '0');

        return $response;
    }

    protected function getAuthenticationUtils(): AuthenticationUtils
    {
        return $this->container->get('security.authentication_utils');
    }

    /**
     * @return array<string|int, string>
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['security.authentication_utils'] = AuthenticationUtils::class;

        return $subscribedServices;
    }
}

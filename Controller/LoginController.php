<?php

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Component\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    const TYPE = Configuration::TYPE_LOGIN;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $communityManager = $this->getCommunityManager();
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            $communityManager->getConfigTypeProperty(self::TYPE, Configuration::TEMPLATE),
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function embedAction(Request $request)
    {
        $communityManager = $this->getCommunityManager();

        $response = $this->render($communityManager->getConfigTypeProperty(self::TYPE, Configuration::EMBED_TEMPLATE));

        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->set(
            HttpCache::HEADER_REVERSE_PROXY_TTL,
            0
        );

        return $response;
    }
}

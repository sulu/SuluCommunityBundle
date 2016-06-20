<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use Sulu\Bundle\CommunityBundle\Validator\User\CompletionInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Validates the current user entity.
 */
class CompletionListener
{
    /**
     * @var RequestAnalyzerInterface
     */
    protected $requestAnalyzer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var CompletionInterface[]
     */
    protected $validators;

    /**
     * CompletionListener constructor.
     *
     * @param RequestAnalyzerInterface $requestAnalyzer
     * @param RouterInterface $router
     * @param TokenStorage $tokenStorage
     * @param array $validators
     */
    public function __construct(
        RequestAnalyzerInterface $requestAnalyzer,
        RouterInterface $router,
        TokenStorage $tokenStorage,
        array $validators
    ) {
        $this->requestAnalyzer = $requestAnalyzer;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->validators = $validators;
    }

    /**
     * Will call a specific user completion validator of a webspace.
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();

        if ($request->isMethod('post')) {
            // don't do anything if it's not a post request
            return;
        }

        $completionUrl = $this->router->generate('sulu_community.completion');
        if ($request->getUri() === $completionUrl) {
            // don't do anything if it's the completion url
            return;
        }

        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if (!$user) {
            // don't do anything if no user is login
            return;
        }

        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $validator = $this->getValidator($webspaceKey);

        if ($validator && !$validator->validate($user, $webspaceKey)) {
            $response = new RedirectResponse($completionUrl);
            $event->setResponse($response);
        }
    }

    /**
     * @param CompletionInterface $validator
     * @param string $webspaceKey
     */
    public function addValidator(CompletionInterface $validator, $webspaceKey)
    {
        $this->validators[$webspaceKey] = $validator;
    }

    /**
     * @param string $webspaceKey
     *
     * @return CompletionInterface
     */
    protected function getValidator($webspaceKey)
    {
        if (!isset($this->validators[$webspaceKey])) {
            return;
        }

        return $this->validators[$webspaceKey];
    }
}

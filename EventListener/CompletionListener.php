<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CommunityBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Sulu\Bundle\CommunityBundle\Validator\User\CompletionInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Validates the current user entity.
 */
class CompletionListener implements EventSubscriberInterface
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
     * @var string
     */
    protected $fragmentPath;

    /**
     * @var CompletionInterface[]
     */
    protected $validators;

    /**
     * CompletionListener constructor.
     *
     * @param CompletionInterface[] $validators
     */
    public function __construct(
        RequestAnalyzerInterface $requestAnalyzer,
        RouterInterface $router,
        TokenStorage $tokenStorage,
        string $fragmentPath,
        array $validators
    ) {
        $this->requestAnalyzer = $requestAnalyzer;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->validators = $validators;
        $this->fragmentPath = $fragmentPath;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    /**
     * Will call a specific user completion validator of a webspace.
     */
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $completionUrl = $this->router->generate('sulu_community.completion');

        if (!$event->isMasterRequest()
            || !$request->isMethodSafe()
            || $request->isXmlHttpRequest()
            || $request->getPathInfo() === $completionUrl
            || $request->getPathInfo() === $this->fragmentPath
        ) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            // don't do anything if no user is login
            return;
        }

        $uriParameters = [];
        if ('sulu_community.confirmation' !== $request->attributes->get('_route')) {
            $uriParameters['re'] = $request->getRequestUri();
        }

        $webspaceKey = $this->requestAnalyzer->getWebspace()->getKey();
        $validator = $this->getValidator($webspaceKey);

        if ($validator && !$validator->validate($user, $webspaceKey)) {
            $completionUrl = $this->router->generate('sulu_community.completion', $uriParameters);

            $response = new RedirectResponse($completionUrl);
            $response->setPrivate();
            $response->setMaxAge(0);
            $event->setResponse($response);
        }
    }

    public function addValidator(CompletionInterface $validator, string $webspaceKey): void
    {
        $this->validators[$webspaceKey] = $validator;
    }

    protected function getValidator(string $webspaceKey): ?CompletionInterface
    {
        if (!isset($this->validators[$webspaceKey])) {
            return null;
        }

        return $this->validators[$webspaceKey];
    }
}

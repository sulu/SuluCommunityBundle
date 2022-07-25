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

use Sulu\Bundle\CommunityBundle\DependencyInjection\Configuration;
use Sulu\Bundle\CommunityBundle\Manager\CommunityManagerInterface;
use Sulu\Bundle\CommunityBundle\Validator\User\CompletionInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Validates the current user entity.
 *
 * @internal Register a validator of type CompletionInterface to change the validation
 *
 * @phpstan-import-type Config from CommunityManagerInterface
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
     * @var TokenStorageInterface
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
     * @var array<string, Config>
     */
    protected $config;

    /**
     * CompletionListener constructor.
     *
     * @param CompletionInterface[] $validators
     * @param array<string, Config> $config
     */
    public function __construct(
        RequestAnalyzerInterface $requestAnalyzer,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        string $fragmentPath,
        array $validators,
        array $config
    ) {
        $this->requestAnalyzer = $requestAnalyzer;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->validators = $validators;
        $this->fragmentPath = $fragmentPath;
        $this->config = $config;
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

        /** @var Webspace|null $webspace */
        $webspace = $this->requestAnalyzer->getWebspace();

        if (!$webspace) {
            return;
        }

        $webspaceKey = $webspace->getKey();

        if (!isset($this->config[$webspaceKey])) {
            return;
        }

        $validator = $this->getValidator($webspaceKey);

        if (!$validator) {
            return;
        }

        $expectedFirewall = $this->config[$webspaceKey][Configuration::FIREWALL] ?? null;
        // TODO find a better way to detect the current firewall
        /** @var string $firewallContext */
        $firewallContext = $request->attributes->get('_firewall_context', '');
        $currentFirewall = \str_replace(
            'security.firewall.map.context.',
            '',
            $firewallContext
        );

        if ($expectedFirewall !== $currentFirewall) {
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

        if (!$validator->validate($user, $webspaceKey)) {
            $uriParameters = [];
            if ('sulu_community.confirmation' !== $request->attributes->get('_route')) {
                $uriParameters['re'] = $request->getRequestUri();
            }

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

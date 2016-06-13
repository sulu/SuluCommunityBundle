<?php

namespace Sulu\Bundle\CommunityBundle\Controller;

use Sulu\Bundle\CommunityBundle\Manager\CommunityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends Controller
{
    /**
     * @var CommunityManager
     */
    protected $communityManager;

    /**
     * @param string $webspaceKey
     *
     * @return CommunityManager
     */
    public function getCommunityManager($webspaceKey = null)
    {
        if ($this->communityManager === null) {
            if (!$webspaceKey) {
                $webspaceKey = $this->get('sulu_core.webspace.request_analyzer')->getWebspace()->getKey();
            }

            $this->communityManager = $this->get(sprintf('sulu_community.%s.community_manager', $webspaceKey));
        }

        return $this->communityManager;
    }

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
     * @param array $custom
     *
     * @return array
     */
    private function getTemplateAttributes($custom = [])
    {
        $defaults = [
            'isCommunityTemplate' => true,
            'extension' => [
                'excerpt' => [
                ],
                'seo' => [
                ],
            ],
            'content' => [],
            'shadowBaseLocale' => null,
        ];

        $requestAnalyzer = $this->get('sulu_core.webspace.request_analyzer');

        $default = array_merge(
            $defaults,
            $this->get('sulu_website.resolver.request_analyzer')->resolve($requestAnalyzer)
        );

        if (!isset($custom['urls'])) {
            $router = $this->get('router');
            $request = $this->get('request_stack')->getCurrentRequest();
            $urls = [];

            if ($request->get('_route')) {
                foreach ($requestAnalyzer->getWebspace()->getLocalizations() as $localization) {
                    $url = $router->generate(
                        $request->get('_route'),
                        $request->get('_route_params')
                    );

                    // will remove locale because it will be added automatically
                    if (preg_match('/^\/[a-z]{2}(-[a-z]{2})?+\/(.*)/', $url)) {
                        $url = substr($url, strlen($localization->getLocale()) + 1);
                    }

                    $urls[$localization->getLocale()] = $url;
                }
            }

            $custom['urls'] = $urls;
        }

        return array_merge(
            $default,
            $custom
        );
    }
}

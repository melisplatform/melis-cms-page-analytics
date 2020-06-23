<?php

namespace MelisCmsPageAnalytics\Listener;


use MelisFront\Listener\MelisFrontSEODispatchRouterAbstractListener;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class MelisCmsPageAnalyticsListener extends MelisFrontSEODispatchRouterAbstractListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            'melisfront_site_dispatch_ready',
            function ($e) {
                //page ID
                $params = $e->getParams();
                $pageId = (int)$params['idpage'];

                //Get the latest visit date and time
                if ($params['renderMode'] == 'front') //only front not back
                {
                    /** @var \MelisCmsPageAnalytics\Service\MelisCmsDefaultPageAnalyticsService $defaultAnalyticsService */
                    $defaultAnalyticsService = $e->getTarget()->serviceManager->get('MelisCmsDefaultPageAnalyticsService');
                    $defaultAnalyticsService->saveAnalyticsData($pageId);
                }
            },
            -10000
        );

        $this->attachEventListener(
            $events,
            '*',
            'melis_front_layout',
            function ($e) {

                $params = $e->getParams();

                if (isset($params['content'])) {

                    $pageId = isset($params['idPage']) ? (int)$params['idPage'] : null;

                    // get the site domain of the page
                    $pageTreeSvc = $e->getTarget()->getServiceManager()->get('MelisEngineTree');
                    $siteData = $pageTreeSvc->getSiteByPageId($pageId);

                    if ($siteData) {

                        $siteId = (int)$siteData->sdom_site_id;
                        $table = $e->getTarget()->getServiceManager()->get('MelisCmsPageAnalyticsDataTable');

                        $analyticsData = $table->getAnalytics($siteId)->current();

                        if ($analyticsData) {
                            $currentAnalyticsKey = $analyticsData->pad_analytics_key;
                            $analyticsData = $table->getAnalytics($siteId, $currentAnalyticsKey)->current();

                            if ($analyticsData) {
                                $script = $analyticsData->pads_js_analytics;
                                if ($script && !empty($script)) {
                                    $script = $script;
                                    $content = str_replace('</head>', $script . '</head>', $params['content']);

                                    return $content;
                                }
                            }
                        }
                    }
                }
            }
        );
    }
}
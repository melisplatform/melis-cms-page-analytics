<?php

namespace MelisCmsPageAnalytics\Listener;


use MelisFront\Listener\MelisFrontSEODispatchRouterAbstractListener;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MelisCmsPageAnalyticsListener extends MelisFrontSEODispatchRouterAbstractListener implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;

    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            ['melisfront_site_dispatch_ready'],
            function ($e) {
                //page ID
                $params = $e->getParams();
                $pageId = (int)$params['idpage'];

                //Get the latest visit date and time
                if ($params['renderMode'] == 'front') //only front not back
                {
                    /** @var \MelisCmsPageAnalytics\Service\MelisCmsDefaultPageAnalyticsService $defaultAnalyticsService */
                    $defaultAnalyticsService = $this->getServiceLocator()->get('MelisCmsDefaultPageAnalyticsService');
                    $defaultAnalyticsService->saveAnalyticsData($pageId);
                }
            },
            -10000);

        $melisLayoutCallBackHandler = $sharedEvents->attach(
            '*',
            ['melis_front_layout'],
            function ($e) {

                $params = $e->getParams();

                if (isset($params['content'])) {

                    $pageId = isset($params['idPage']) ? (int)$params['idPage'] : null;

                    // get the site domain of the page
                    $pageTreeSvc = $this->getServiceLocator()->get('MelisEngineTree');
                    $siteData = $pageTreeSvc->getSiteByPageId($pageId);

                    if ($siteData) {

                        $siteId = (int)$siteData->sdom_site_id;
                        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');


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
        $this->listeners[] = $callBackHandler;
        $this->listeners[] = $melisLayoutCallBackHandler;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
        return $this;
    }

}
     

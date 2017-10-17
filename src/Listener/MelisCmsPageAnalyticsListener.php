<?php


namespace MelisCmsPageAnalytics\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use MelisFront\Listener\MelisFrontSEODispatchRouterAbstractListener;
use Zend\Stdlib\ParametersInterface;
use Zend\Mvc\MvcEvent;
class MelisCmsPageAnalyticsListener extends MelisFrontSEODispatchRouterAbstractListener implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
        return $this;
    }
    
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'melisfront_site_dispatch_ready'
            ),
            function($e){

                //page ID
                $params  = $e->getParams();
                $pageId  = (int) $params['idpage'];


                //Get the latest visit date and time
                if($params['renderMode'] == 'front') //only front not back
                {
                    $defaultAnalyticsService = $this->getServiceLocator()->get('MelisCmsDefaultPageAnalyticsService');
                    $defaultAnalyticsService->saveAnalyticsData($pageId);
                }

            },
            -10000);

        $melisLayoutCallBackHandler = $sharedEvents->attach(
            '*',
            array(
                'melis_front_layout'
            ),
            function($e) {

                $params = $e->getParams();

                if(isset($params['content'])) {

                    $pageId = isset($params['idPage']) ? (int) $params['idPage'] : null;

                    // get the site domain of the page
                    $pageTreeSvc = $this->getServiceLocator()->get('MelisEngineTree');
                    $siteData    = $pageTreeSvc->getSiteByPageId($pageId);

                    if($siteData) {

                        $siteId        = (int) $siteData->sdom_site_id;
                        $table         = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
                        $analyticsData = $table->getAnalyticsDataBySiteId($siteId)->current();

                        if($analyticsData) {

                            $script = $analyticsData->pad_js_analytics;
                            if($script && !empty($script)) {
                                $script = '<script>' . $script . '</script>';
                                $content = str_replace('</head>', $script.'</head>', $params['content']);

                                return $content;
                            }
                        }
                    }
                }
            }
        );
        $this->listeners[] = $callBackHandler;
        $this->listeners[] = $melisLayoutCallBackHandler;
    }

}
     

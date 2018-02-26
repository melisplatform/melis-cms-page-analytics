<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisCoreGeneralListener;

/**
 * This listener listens to MelisCmsPageAnalytics events in order to add entries in the
 * flash messenger
 */
class MelisCmsPageAnalyticsFlashMessengerListener extends MelisCoreGeneralListener implements ListenerAggregateInterface
{

    /**
     * Handles the flash messenger event listener
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            'MelisCmsPageAnalytics',
            array(
                'melis_cms_page_analytics_flash_messenger'
            ),
            function($e){

                $sm = $e->getTarget()->getServiceLocator();
                $flashMessenger = $sm->get('MelisCoreFlashMessenger');

                $params = $e->getParams();
                $params['textTitle']   = $params['title'];
                $params['textMessage'] = $params['message'];
                $results = $e->getTarget()->forward()->dispatch(
                    'MelisCore\Controller\MelisFlashMessenger',
                    array_merge(array('action' => 'log'), $params)
                )->getVariables();
            },
            -1000);

        $this->listeners[] = $callBackHandler;
    }
}
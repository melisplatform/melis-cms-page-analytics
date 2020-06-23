<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisGeneralListener;

/**
 * This listener listens to MelisCmsPageAnalytics events in order to add entries in the
 * flash messenger
 */
class MelisCmsPageAnalyticsFlashMessengerListener extends MelisGeneralListener
{

    /**
     * Handles the flash messenger event listener
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            'MelisCmsPageAnalytics',
            'melis_cms_page_analytics_flash_messenger',
            [$this, 'logMessages'],
            -1000
        );
    }
}
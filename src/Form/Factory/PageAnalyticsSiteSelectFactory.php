<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Form\Factory;

use MelisCore\Form\Factory\MelisSelectFactory;
use Laminas\ServiceManager\ServiceManager;

class PageAnalyticsSiteSelectFactory extends MelisSelectFactory
{
    protected function loadValueOptions(ServiceManager $serviceManager){
        $valueoptions   = array();
        $tableSites     = $serviceManager->get('MelisEngineTableSite');
        $sites          = $tableSites->getSites();

        foreach($sites as $site) {
            $siteLabel = $site->site_label ?? $site->site_name;
            $valueoptions[$site->site_id] = $siteLabel;
        }
        return $valueoptions;
    }
}
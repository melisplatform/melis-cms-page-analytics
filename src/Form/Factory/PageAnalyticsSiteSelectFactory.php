<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Form\Factory;

use MelisCore\Form\Factory\MelisSelectFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class PageAnalyticsSiteSelectFactory extends MelisSelectFactory
{
    protected function loadValueOptions(ServiceLocatorInterface $formElementManager){
        $valueoptions   = array();
        $serviceManager = $formElementManager->getServiceLocator();
        $tableSites     = $serviceManager->get('MelisEngineTableSite');
        $sites          = $tableSites->getSites();

        foreach($sites as $site) {
            $valueoptions[$site->site_id] = $site->site_name;
        }
        return $valueoptions;
    }
}
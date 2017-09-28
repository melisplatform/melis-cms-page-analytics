<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Form\Factory;
use Zend\ServiceManager\ServiceLocatorInterface;
use MelisCore\Form\Factory\MelisSelectFactory;


class PageAnalyticsSelectFactory extends MelisSelectFactory
{
    protected function loadValueOptions(ServiceLocatorInterface $formElementManager)
    {
        $serviceManager   = $formElementManager->getServiceLocator();
        $config = $serviceManager->get('MelisCoreConfig');
        $pageAnalyticsCfg = $config->getItem('meliscms/datas/page_analytics');

        $translator = $serviceManager->get('translator');

        $valueoptions = array();

        foreach($pageAnalyticsCfg as $key => $cfg) {
            $valueoptions[$key] = $translator->translate($cfg['conf']['name']);
        }


        return $valueoptions;
    }


}
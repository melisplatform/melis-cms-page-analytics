<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisCmsPageAnalytics\Service\MelisCmsDefaultPageAnalyticsService;

class MelisCmsDefaultPageAnalyticsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $service = new MelisCmsDefaultPageAnalyticsService();
        $service->setServiceLocator($sl);

        return $service;
    }

}
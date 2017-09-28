<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Listener\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisCmsPageAnalytics\Listener\MelisCmsPageAnalyticsListener;

class MelisCmsPageAnalyticsListenerFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $sl)
	{ 
    	$listener = new MelisCmsPageAnalyticsListener($sl);
	    return $listener;
	}
}
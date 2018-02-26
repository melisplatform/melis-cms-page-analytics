<?php

namespace MelisCmsPageAnalytics\Service;

use MelisCore\Service\MelisCoreGeneralService;
use MelisCmsPageAnalytics\Service\MelisCmsPageAnalyticsServiceInterface;

class MelisCmsPageAnalyticsService extends MelisCoreGeneralService implements MelisCmsPageAnalyticsServiceInterface
{	

	public function getAnalytics($siteId)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melis_cms_page_analytics_get_current_analytics_start', $arrayParameters);

        $siteId = (int) $arrayParameters['siteId'];
        $data   = null;
        $table  = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
        $data   = $table->getAnalytics($siteId)->current();

        if($data) {

            $currentAnalyticsKey = $data->pad_analytics_key;
            $data   = $table->getAnalytics($siteId, $currentAnalyticsKey)->current();

            if($data) {
                $data->pads_settings = (object) unserialize($data->pads_settings);
            }

        }

        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = $data;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melis_cms_page_analytics_get_current_analytics_end', $arrayParameters);

        return $arrayParameters['results'];
    }

}
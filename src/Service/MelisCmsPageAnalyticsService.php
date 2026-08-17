<?php

namespace MelisCmsPageAnalytics\Service;

use MelisEngine\Service\MelisEngineGeneralService;
use MelisCmsPageAnalytics\Service\MelisCmsPageAnalyticsServiceInterface;

class MelisCmsPageAnalyticsService extends MelisEngineGeneralService implements MelisCmsPageAnalyticsServiceInterface
{	

	public function getAnalytics($siteId)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melis_cms_page_analytics_get_current_analytics_start', $arrayParameters);

        $siteId = (int) $arrayParameters['siteId'];
        $data   = null;
        $table  = $this->getServiceManager()->get('MelisCmsPageAnalyticsDataTable');
        $data   = $table->getAnalytics($siteId)->current();

        if($data) {

            $currentAnalyticsKey = $data->pad_analytics_key;
            $data   = $table->getAnalytics($siteId, $currentAnalyticsKey)->current();

            if($data) {
                $data->pads_settings = (object) unserialize($data->pads_settings);

                // The GA service-account key now lives in its own column `pads_ga_private_key`
                // (JSON CONTENT), no longer as a file path inside the serialized blob — a file
                // under the vendor tree is wiped on deploy and not shared across pods. When the
                // column is filled we overwrite the historical field so every downstream caller
                // transparently gets the CONTENT; init accepts content-OR-path (backward compat:
                // a legacy path still stored in pads_settings keeps working when the column is empty).
                if (!empty($data->pads_ga_private_key)) {
                    $data->pads_settings->google_analytics_private_key = $data->pads_ga_private_key;
                }
            }

        }

        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = $data;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melis_cms_page_analytics_get_current_analytics_end', $arrayParameters);

        return $arrayParameters['results'];
    }

}
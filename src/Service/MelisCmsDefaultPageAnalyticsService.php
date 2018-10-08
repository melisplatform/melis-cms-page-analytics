<?php

namespace MelisCmsPageAnalytics\Service;

use MelisCore\Service\MelisCoreGeneralService;

class MelisCmsDefaultPageAnalyticsService extends MelisCoreGeneralService
{

    /**
     * This will save the information of the visitor except the IP address
     * @param int $pageId
     * @return mixed
     */
    public function saveAnalyticsData($pageId)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melis_cms_default_page_analytics_save_start', $arrayParameters);

        $pageAnalyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsTable');

        $sessionCookie   = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : session_id();
        $currentDateTime =  date("Y-m-d H:i:s");

        $pageId   = $arrayParameters['pageId'];

        if($pageId) {

            $pageData = $pageAnalyticsTable->getDataBySessionAndPageId( (int) $pageId, $sessionCookie)->toArray();

            if(!empty($pageData)) {
                $pageData = end($pageData);
                // check if the today
                if(!$this->isToday($pageData['ph_date_visit'])) {
                    $pageAnalyticsTable->save([
                        'ph_page_id'    => $pageId,
                        'ph_session_id' => $sessionCookie,
                        'ph_date_visit' => $currentDateTime
                    ]);
                }
            }
            else {
                $pageAnalyticsTable->save([
                    'ph_page_id'    => $pageId,
                    'ph_session_id' => $sessionCookie,
                    'ph_date_visit' => $currentDateTime
                ]);
            }
        }


        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = true;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melis_cms_default_page_analytics_save_end', $arrayParameters);

        return $arrayParameters['results'];
    }

    private function isToday($d)
    {
        $strDate = date_create(date("Y-m-d", strtotime($d)));
        $today = date_create(date('Y-m-d'));

        $diff = date_diff($strDate, $today);

        if( (int) $diff->days == 0)
            return true;
        else
            return false;
    }

}
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
        $pageId = $arrayParameters['pageId'];
        if (!empty($pageId)) {
            /**
             * Check for the page's status: Only record views for active pages.
             * @var \MelisEngine\Model\Tables\MelisPagePublishedTable $pagePublished
             */
            $pagePublished = $this->getServiceLocator()->get('MelisEngineTablePagePublished');
            $pagePublished = current($pagePublished->getEntryById($pageId)->toArray());
            if (!empty($pagePublished['page_status'])) {
                /** Page is active */
                $sessionCookie = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : session_id();
                $currentDateTime = date("Y-m-d H:i:s");

                $pageAnalyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsTable');
                $pageData = $pageAnalyticsTable->getDataBySessionAndPageId((int)$pageId, $sessionCookie)->toArray();

                $siteId = $this->getSiteIdByPageId($pageId);

                if (!empty($pageData)) {
                    $pageData = end($pageData);
                    // check if the today
                    if (!$this->isToday($pageData['ph_date_visit'])) {
                        $pageAnalyticsTable->save([
                            'ph_page_id' => $pageId,
                            'ph_session_id' => $sessionCookie,
                            'ph_date_visit' => $currentDateTime,
                            'ph_site_id' => $siteId
                        ]);
                    }
                } else {
                    $pageAnalyticsTable->save([
                        'ph_page_id' => $pageId,
                        'ph_session_id' => $sessionCookie,
                        'ph_date_visit' => $currentDateTime,
                        'ph_site_id' => $siteId
                    ]);
                }
            }
        }


        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = true;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melis_cms_default_page_analytics_save_end', $arrayParameters);

        return $arrayParameters['results'];
    }

    /**
     * Get the correct site id
     *
     * @param $pageId
     * @return int
     */
    private function getSiteIdByPageId($pageId)
    {
        $siteId = 0;

        if (!empty($pageId)) {
            /**
             * check first if there is data on page saved
             */
            $pageSaved = $this->getServiceLocator()->get('MelisEngineTablePageSaved');
            $pageSavedData = $pageSaved->getEntryById($pageId)->current();
            if (!empty($pageSavedData)) {
                $tplId = $pageSavedData->page_tpl_id;
            } else {
                //try to get the data from the page published
                $pagePublished = $this->getServiceLocator()->get('MelisEngineTablePagePublished');
                $pagePublishedData = $pagePublished->getEntryById($pageId)->current();
                $tplId = $pagePublishedData->page_tpl_id;
            }
            if (!empty($tplId)) {
                $template = $this->getServiceLocator()->get('MelisEngineTableTemplate');
                $tplData = $template->getEntryById($tplId)->current();
                if (!empty($tplData)) {
                    $siteId = $tplData->tpl_site_id;
                }
            }
        }

        return $siteId;
    }

    private function isToday($d)
    {
        $strDate = date_create(date("Y-m-d", strtotime($d)));
        $today = date_create(date('Y-m-d'));

        $diff = date_diff($strDate, $today);

        if ((int)$diff->days == 0)
            return true;
        else
            return false;
    }
}

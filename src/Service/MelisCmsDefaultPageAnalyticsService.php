<?php

namespace MelisCmsPageAnalytics\Service;


use MelisEngine\Service\MelisEngineGeneralService;

class MelisCmsDefaultPageAnalyticsService extends MelisEngineGeneralService
{
    /**
     * SECURITY (audit PA-H1) : plafond dur d'insertions par page et par jour. La clé visiteur
     * contient de la donnée client (UA) ; sans plafond, la faire tourner permettrait encore une
     * ligne par requête. Avec lui, la croissance de la table est bornée quoi qu'envoie le client.
     */
    const DAILY_INSERT_CAP_PER_PAGE = 2000;

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

        $pageTreeSvc = $this->getServiceManager()->get('MelisEngineTree');
        $siteData = $pageTreeSvc->getSiteByPageId($pageId);

        if (empty($siteData))
            return null;
        
        $siteId = (int)$siteData->site_id;

        // Visits are only counted when an analytics module is selected for the site (0011054):
        // no module selected yet, or « No analytics module », means nothing is recorded.
        // Read the raw key: MelisCmsPageAnalyticsService::getAnalytics() returns nothing for the
        // « none » pseudo-module (it has no settings row), so it cannot tell the cases apart.
        $siteAnalytics = $this->getServiceManager()->get('MelisCmsPageAnalyticsDataTable')->getAnalytics($siteId)->current();
        $analyticsKey = $siteAnalytics ? (string) $siteAnalytics->pad_analytics_key : '';
        if ($analyticsKey === '' || $analyticsKey === 'melis_cms_no_analytics')
            return null;

        $table = $this->getServiceManager()->get('MelisCmsPageAnalyticsService');
        $data = $table->getAnalytics($siteId);

        $analyticsViewId = null;
        $tokenInfo = null;
        if ($this->getServiceManager()->has('GoogleAnalyticsAPIService')) {
            /**
             * Getting access token for API calls
             * @var GoogleAnalyticsAPIService $googleAPIService
             */
            $googleAPIService = $this->getServiceManager()->get('GoogleAnalyticsAPIService');
            $response = $googleAPIService->setAnalyticsReportingService($siteId);
            if ($response) {
                $tokenInfo = base64_encode(json_encode($googleAPIService->getAccessToken()));
            }

            if ($data) {
                $analyticsViewId = $data->pads_settings->google_analytics_view_id ?? false;
            }
        }

        if(empty($tokenInfo) || empty($analyticsViewId)) {
            if (!empty($pageId)) {
                /**
                 * Check for the page's status: Only record views for active pages.
                 * @var \MelisEngine\Model\Tables\MelisPagePublishedTable $pagePublished
                 */
                $pagePublished = $this->getServiceManager()->get('MelisEngineTablePagePublished');
                $pagePublished = current($pagePublished->getEntryById($pageId)->toArray());
                if (!empty($pagePublished['page_status'])) {
                    /** Page is active */
                    /**
                     * SECURITY (audit PA-H1/PA-M2) : la clé de déduplication était le cookie
                     * PHPSESSID FOURNI PAR LE CLIENT — le faire tourner donnait une insertion
                     * par requête anonyme (DoS de stockage), et l'identifiant de session brut
                     * des visiteurs était stocké puis affiché en BO. La clé est désormais
                     * dérivée CÔTÉ SERVEUR et hachée (aucune session serveur n'est démarrée,
                     * aucune donnée brute stockée) : REMOTE_ADDR (volontairement pas
                     * X-Forwarded-For, falsifiable) + User-Agent + jour. L'UA restant de la
                     * donnée client, un plafond dur par page et par jour borne la table quoi
                     * qu'il arrive (cf. isDailyCapReached).
                     */
                    $visitorKey = $this->makeVisitorKey();
                    $currentDateTime = date("Y-m-d H:i:s");

                    $pageAnalyticsTable = $this->getServiceManager()->get('MelisCmsPageAnalyticsTable');
                    $pageData = $pageAnalyticsTable->getDataBySessionAndPageId((int)$pageId, $visitorKey)->toArray();

                    $siteId = $this->getSiteIdByPageId($pageId);

                    if (!empty($pageData)) {
                        $pageData = end($pageData);
                        // check if the today
                        if (!$this->isToday($pageData['ph_date_visit']) && !$this->isDailyCapReached((int)$pageId)) {
                            $pageAnalyticsTable->save([
                                'ph_page_id' => $pageId,
                                'ph_session_id' => $visitorKey,
                                'ph_date_visit' => $currentDateTime,
                                'ph_site_id' => $siteId
                            ]);
                        }
                    } elseif (!$this->isDailyCapReached((int)$pageId)) {
                        $pageAnalyticsTable->save([
                            'ph_page_id' => $pageId,
                            'ph_session_id' => $visitorKey,
                            'ph_date_visit' => $currentDateTime,
                            'ph_site_id' => $siteId
                        ]);
                    }
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
     * SECURITY (audit PA-H1) : clé visiteur dérivée côté serveur, hachée (sha256 hex, 64 car.
     * dans un VARCHAR(100)), jamais issue d'un cookie. Le jour fait partie de la clé, la
     * déduplication « une visite par jour » est donc intrinsèque.
     *
     * @return string
     */
    private function makeVisitorKey()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

        return hash('sha256', $ip . '|' . $ua . '|' . date('Y-m-d'));
    }

    /**
     * SECURITY (audit PA-H1) : borne dure de croissance — au-delà de
     * DAILY_INSERT_CAP_PER_PAGE lignes pour (page, jour), on cesse d'enregistrer.
     * Fail-closed : au moindre doute (erreur SQL), on n'écrit pas.
     *
     * @param int $pageId
     * @return bool
     */
    private function isDailyCapReached($pageId)
    {
        try {
            $db = $this->getServiceManager()->get(\Laminas\Db\Adapter\AdapterInterface::class);
            $row = $db->query(
                'SELECT COUNT(*) AS c FROM melis_cms_page_analytics WHERE ph_page_id = ? AND ph_date_visit >= ?',
                [$pageId, date('Y-m-d 00:00:00')]
            )->current();

            return ((int) ($row['c'] ?? 0)) >= self::DAILY_INSERT_CAP_PER_PAGE;
        } catch (\Throwable $e) {
            return true;
        }
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
            $pageSaved = $this->getServiceManager()->get('MelisEngineTablePageSaved');
            $pageSavedData = $pageSaved->getEntryById($pageId)->current();
            if (!empty($pageSavedData)) {
                $tplId = $pageSavedData->page_tpl_id;
            } else {
                //try to get the data from the page published
                $pagePublished = $this->getServiceManager()->get('MelisEngineTablePagePublished');
                $pagePublishedData = $pagePublished->getEntryById($pageId)->current();
                $tplId = $pagePublishedData->page_tpl_id;
            }
            if (!empty($tplId)) {
                $template = $this->getServiceManager()->get('MelisEngineTableTemplate');
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

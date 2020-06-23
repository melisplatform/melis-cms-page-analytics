<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */
namespace MelisCmsPageAnalytics\Controller;

use MelisCore\Controller\MelisAbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
class MelisCmsPageAnalyticsPageDetailsToolController extends MelisAbstractActionController
{
    private $pageId = null;

    public function toolContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $pageHitId = (int) $this->params()->fromQuery('idPage', $this->params()->fromQuery('pageHitId'));
        $pageHitId  = (int) $this->params()->fromQuery('idPage', $this->params()->fromQuery('pageHitId'));
        $pageTree   = $this->getServiceManager()->get('MelisEngineTree');
        $pageUrl    = $pageTree->getPageLink($pageHitId, true);
        $pageTreeSvc = $this->getServiceManager()->get('MelisEngineTree');
        $siteData    = $pageTreeSvc->getSiteByPageId($pageHitId);
        $pageTree = $this->getServiceManager()->get('MelisEngineTree');
        $pageUrl = $pageTree->getPageLink($pageHitId, true);
        $pageTreeSvc = $this->getServiceManager()->get('MelisEnginePage');
        $siteData = $pageTreeSvc->getDatasPage($pageHitId,'saved');
        $errMsg = "";
        $display = null;
        $displayScript = null;

        $pageTree = $siteData->getMelisPageTree();

        if($pageTree->page_type != "FOLDER") {

            $siteId = null;
            if ($siteData) {
                $siteId = !empty($siteData->getMelisTemplate()) ? (int)$siteData->getMelisTemplate()->tpl_site_id : null;
            }
            $this->pageId = $pageHitId;
            $table = $this->getServiceManager()->get('MelisCmsPageAnalyticsDataTable');
            $curData = $table->getAnalytics($siteId)->current();

            if ($curData) {
                $config = $this->getServiceManager()->get('MelisCoreConfig');
                $currentAnalytics = $curData->pad_analytics_key;
                $hasAnalyticsConfig = $config->getItem('meliscms/datas/page_analytics/' . $currentAnalytics);

                if ($hasAnalyticsConfig) {
                    $pageAnalyticsData = $hasAnalyticsConfig;
                    $forward = $pageAnalyticsData['interface']['analytics_for_page']['forward'];
                    $display = $this->getTool()->getViewContent($forward);

                    $display = str_replace(array(
                        'sDom : "<', 'rip>>"', 'return "<div>',
                        '<endaction/></div>";',
                        '"<a class="btn btn-default melis-cms-page-analytics-refresh',
                        'fa-refresh"></i></a>"',
                        '.melis_cms_page_analytics_page_search input[type="search"]'
                    ), array(
                        "sDom : '<", "rip>>'",
                        "return '<div>",
                        "<endaction/></div>';",
                        "'<a class=\"btn btn-default melis-cms-page-analytics-refresh",
                        "fa-refresh\"></i></a>'",
                        ".melis_cms_page_analytics_page_search input[type='search']"
                    ), $display);
                    // get the url the page
                    $pageTree = $this->getServiceManager()->get('MelisEngineTree');
                    $pageUrl = $pageTree->getPageLink($pageHitId, true);
                } else {
                    $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_inactive_module');
                }
            } else {
                $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_no_module_set');
            }
        }else{
            $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_folder_message');
        }

        $view = new ViewModel();
        $view->melisKey  = $melisKey;
        $view->pageHitId = $pageHitId;
        $view->display   = $display;
        $view->pageUrl   = $pageUrl;
        $view->errMsg    = $errMsg;
        return $view;
    }
    public function toolContainerPageAnalyticsPageDetailsAction()
    {
        $melisKey = $this->getMelisKey();
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    /*
     * Page Detail View Container
     */
    public function toolHeaderContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $pageName = $this->params()->fromQuery('tabName');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->title = $pageName;
        return $view;
    }
    /**
     * Returns analytics of a site
     * @param siteId
     *
     * return array
     */
    public function getAnalytics($siteId)
    {
        $success = 0;
        $error   = array();
        $data    = array();
        $analytics = $this->serviceLocator()->get("MelisPageAnalytics");
        $data      = $analytics->getAnalyticsBySiteId($siteId);
        $data      = array();
        return $data;
    }
    /*
     * Page Hit Page Detail Content Container
     */
    public function toolContentContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $pageHitId = (int) $this->params()->fromQuery('idPage', $this->params()->fromQuery('pageHitId'));
        $columns = $this->getTool()->getColumns();

        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->tableColumns = $columns;
        $view->pageHitId = $pageHitId;

        // Setting first column's (ID) default Order to descending
        $view->getToolDataTableConfig = $this->getTool()->getDataTableConfiguration('#'. $pageHitId .'_tableMelisCmsPageAnalyticsPageDetails', true, false, array('order' => '[[0, "desc"]]'));

        return $view;
    }
    /*
     * getMelisKey method
     */
    private function getMelisKey()
    {
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey'), null);
        return $melisKey;
    }
    private function melisCmsPageAnalytcisTable()
    {
        $table = $this->getServiceManager()->get('MelisCmsPageAnalyticsTable');
        return $table;
    }
    private function getTool()
    {
        $toolSvc = $this->getServiceManager()->get('MelisCoreTool');
        $toolSvc->setMelisToolKey('MelisCmsPageAnalytics', 'MelisCmsPageAnalytics_page_details');
        return $toolSvc;
    }
    /*
     * Returns the data of the current user
     * @return null
     */
    private function getLoggedInUserInfo()
    {
        $authService = $this->getServiceManager()->get('MelisCoreAuth');
        if($authService->hasIdentity()) {
            return $authService->getIdentity();
        }
        return null;
    }
    public function getMelisCmsPageAnalyticsPageDetailsDataAction()
    {
        $request = $this->getRequest();

        $dataCount = 0;
        $dataFilteredCount = 0;
        $tableData = array();
        $draw = 0;

        if($request->isPost()) {
            $post    = $request->getPost()->toArray();
            $columns = array_keys($this->getTool()->getColumns());
            $draw           = (int) $post['draw'];
            $selColOrder    = $columns[(int) $post['order'][0]['column']];
            $orderDirection = isset($post['order']['0']['dir']) ? strtoupper($post['order']['0']['dir']) : 'DESC';
            $searchValue    = isset($post['search']['value']) ? $post['search']['value'] : null;
            $searchableCols = $this->getTool()->getSearchableColumns();
            $start          = (int) $post['start'];
            $length         = (int) $post['length'];
            $pageHitId      = (int) $post['pageId'];

            $data = $this->melisCmsPageAnalytcisTable()->getDataByPageId($pageHitId, $searchValue, $searchableCols, $selColOrder , $orderDirection , $start, $length)->toArray();
            $dataCount = $this->melisCmsPageAnalytcisTable()->getTotalData();
            $dataFilteredCount = $this->melisCmsPageAnalytcisTable()->getTotalFiltered();
            $tableData = $data;
            for($ctr = 0; $ctr < count($tableData); $ctr++) {
                // apply text limits
                foreach($tableData[$ctr] as $vKey => $vValue)
                {
                    $tableData[$ctr][$vKey] = $this->getTool()->limitedText($vValue, 80);
                }

                $tableData[$ctr]['DT_RowId']      = $tableData[$ctr]['ph_id'];
            }

        }
        $response = [
            'draw' => $draw,
            'data' => $tableData,
            'recordsFiltered' => $dataFilteredCount,
            'recordsTotal' => $dataCount
        ];
        return new JsonModel($response);
    }
    /*
     * Limit data in the table
     */
    public function toolContentTableLimitAction()
    {
        return new ViewModel();
    }
    /*
     * Search Data in the table
     */
    public function toolContentTableSearchPageAction()
    {
        return new ViewModel();
    }
    public function toolContentTableRefreshPageAction()
    {
        return new ViewModel();
    }
}
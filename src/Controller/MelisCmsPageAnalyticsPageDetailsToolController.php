<?php

/**
* Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisCmsPageAnalytics\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;


class MelisCmsPageAnalyticsPageDetailsToolController extends AbstractActionController
{

    private $pageId = null;
 
   public function toolContainerAction()
   {
       $melisKey = $this->getMelisKey();
       $pageHitId = (int) $this->params()->fromQuery('idPage', $this->params()->fromQuery('pageHitId'));


       $pageHitId  = (int) $this->params()->fromQuery('idPage', $this->params()->fromQuery('pageHitId'));
       $pageTree   = $this->getServiceLocator()->get('MelisEngineTree');
       $pageUrl    = $pageTree->getPageLink($pageHitId, true);
       $pageTreeSvc = $this->getServiceLocator()->get('MelisEngineTree');
       $siteData    = $pageTreeSvc->getSiteByPageId($pageHitId);
       $siteId      = null;

       if($siteData) {
           $siteId = (int) $siteData->sdom_site_id;
       }

       $this->pageId = $pageHitId;

       $table         = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
       $curData       = $table->getAnalytics($siteId)->current();
       $display       = null;
       $displayScript = null;
       if($curData) {
           $currentAnalytics = $curData->pad_analytics_key;
           $config           = $this->getServiceLocator()->get('MelisCoreConfig');

           if($currentAnalytics) {
               $pageAnalyticsData = $config->getItem('meliscms/datas/page_analytics');
               $pageAnalyticsData = $pageAnalyticsData[$currentAnalytics];

               if($pageAnalyticsData) {

                   $forward = $pageAnalyticsData['interface']['analytics_for_page']['forward'];
                   $display = $this->getTool()->getViewContent($forward);
                   $display = str_replace(array(
                       'sDom : "<', 'rip>>"', 'return "<div>',
                       '<endaction/></div>";',
                       '"<a class="btn btn-default melis-cms-page-analytics-refresh',
                       'fa-refresh"></i></a>"',
                       '(".search input[type="search"]")'
                   ), array(
                       "sDom : '<", "rip>>'",
                       "return '<div>",
                       "<endaction/></div>';",
                       "'<a class=\"btn btn-default melis-cms-page-analytics-refresh",
                       "fa-refresh\"></i></a>'",
                       "(\".search input[type='search']\")"
                   ), $display);

                   // get the url the page
                   $pageTree = $this->getServiceLocator()->get('MelisEngineTree');
                   $pageUrl  = $pageTree->getPageLink($pageHitId, true);

               }
           }

       }


       $view = new ViewModel();

       $view->melisKey  = $melisKey;
       $view->pageHitId = $pageHitId;
       $view->display   = $display;
       $view->pageUrl   = $pageUrl;


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
    
    $view->getToolDataTableConfig = $this->getTool()->getDataTableConfiguration('#'. $pageHitId .'_tableMelisCmsPageAnalyticsPageDetails');
    $view->pageHitId = $pageHitId;
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
      $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsTable');
      return $table;
  }

  private function getTool()
  {
      $toolSvc = $this->getServiceLocator()->get('MelisCoreTool');
      $toolSvc->setMelisToolKey('MelisCmsPageAnalytics', 'MelisCmsPageAnalytics_page_details');
      return $toolSvc;
  }
  /*
   * Returns the data of the current user
   * @return null
   */
  private function getLoggedInUserInfo()
  {
      $authService = $this->getServiceLocator()->get('MelisCoreAuth');
      if($authService->hasIdentity()) {
          return $authService->getIdentity();
      }

      return null;
  }
  public function getMelisCmsPageAnalyticsPageDetailsDataAction()
  {
    $data  = $this->melisCmsPageAnalytcisTable()->fetchAll()->toArray();

    $request = $this->getRequest();
    $dataCount = 0;
    $dataFilteredCount = 0;
    $tableData = array();
    $draw = 0;
    if($request->isPost()) {

        $post    = get_object_vars($request->getPost());
        $columns = array_keys($this->getTool()->getColumns());
        $draw           = (int) $post['draw'];
        $selColOrder    = $columns[(int) $post['order'][0]['column']];
        $orderDirection = isset($post['order']['0']['dir']) ? strtoupper($post['order']['0']['dir']) : 'DESC';
        $searchValue    = isset($post['search']['value']) ? $post['search']['value'] : null;
        $searchableCols = $this->getTool()->getSearchableColumns();
        $start          = (int) $post['start'];
        $length         = (int) $post['length'];
        $pageHitId      = (int) $post['pageId'] ?: $this->pageId;

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
  public function toolContentTableSearchAction()
  {
      return new ViewModel();
  }
   public function toolContentTableRefreshAction()
  {
     return new ViewModel();
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
}
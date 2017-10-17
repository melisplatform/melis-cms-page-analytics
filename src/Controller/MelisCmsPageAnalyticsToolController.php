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
use MelisCore\Service\MelisCoreRightsService;

class MelisCmsPageAnalyticsToolController extends AbstractActionController
{

    public function toolContainerAction()
    {
        $melisKey = $this->getMelisKey();

        $view = new ViewModel();
        $view->melisKey = $melisKey;

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

    private function getTool()
    {
        $toolSvc = $this->getServiceLocator()->get('MelisCoreTool');
        $toolSvc->setMelisToolKey('MelisCmsPageAnalytics', 'MelisCmsPageAnalytics_tool');
        return $toolSvc;
    }

    private function melisCmsPageAnalytcisTable()
    {
        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsTable');
        return $table;
    }

    //Tool HeaderContainer Action
    public function toolHeaderContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->title = $this->getTool()->getTranslation('tr_meliscms_page_analytics_title');

        return $view;
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

    /*
    * View Details of data in the table
    */
    public function toolContentTableActionViewAction()
    {
        return new ViewModel();
    }

    /*
     * Zone Refresh
     */
    public function toolContentTableRefreshAction()
    {
        return new ViewModel();
    }

    public function toolContentContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $hasAccessAnalytics = $this->hasAccess('meliscms_page_analytics_site_analytics_tab');
        $hasAccessAnalyticsSettings = $this->hasAccess('meliscms_page_analytics_settings_tab');

        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->hasAccessAnalytics = $hasAccessAnalytics;
        $view->hasAccessAnalyticsSettings = $hasAccessAnalyticsSettings;
        return $view;
    }

    public function toolDefaultPageAnalyticsTableAction()
    {
        $melisKey = $this->getMelisKey();
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab');
        $columns = $this->getTool()->getColumns();

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->tableColumns = $columns;
        $view->getToolDataTableConfig = $this->getTool()->getDataTableConfiguration('#tableMelisCmsPageAnalytics');
        $view->hasAccess = $hasAccess;
        return $view;
    }


    public function getMelisCmsPageAnalyticsDataAction()
    {
        $data = $this->melisCmsPageAnalytcisTable()->fetchAll()->toArray();

        $request = $this->getRequest();

        $dataCount = 0;
        $dataFilteredCount = 0;
        $tableData = array();
        $draw = 0;

        if ($request->isPost()) {

            $post = get_object_vars($request->getPost());

            $columns = array_keys($this->getTool()->getColumns());
            $draw = (int)$post['draw'];
            $selColOrder = $columns[(int)$post['order'][0]['column']];
            $orderDirection = isset($post['order']['0']['dir']) ? strtoupper($post['order']['0']['dir']) : 'DESC';
            $searchValue = isset($post['search']['value']) ? $post['search']['value'] : null;
            $searchableCols = $this->getTool()->getSearchableColumns();
            $start = (int)$post['start'];
            $length = (int)$post['length'];

            $data = $this->melisCmsPageAnalytcisTable()->getData($searchValue, $searchableCols, $selColOrder, $orderDirection, $start, $length)->toArray();
            $dataCount = $this->melisCmsPageAnalytcisTable()->getTotalData();
            $dataFilteredCount = $this->melisCmsPageAnalytcisTable()->getTotalFiltered();
            $tableData = $data;

            for ($ctr = 0; $ctr < count($tableData); $ctr++) {
                // apply text limits
                foreach ($tableData[$ctr] as $vKey => $vValue) {
                    $tableData[$ctr][$vKey] = $this->getTool()->limitedText($vValue, 80);
                }

                $tableData[$ctr]['DT_RowId'] = $tableData[$ctr]['ph_id'];

            }
        }
        $response = [
            'draw' => $draw,
            'data' => $tableData,
            'recordsFiltered' => $dataFilteredCount,
            'recordsTotal' => $dataCount,

        ];
        return new JsonModel($response);
    }

    private function getForm()
    {
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $formConfig = $config->getItem('meliscms/forms/select_page_analytic_form');
        $factory = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');

        $factory->setFormElementManager($formElements);

        $form = $factory->createForm($formConfig);

        return $form;
    }

    public function saveAction()
    {
        $success = 0;
        $title = 'tr_melis_cms_page_analytics';
        $message = 'tr_meliscms_page_analytics_settings_select_save_ko';
        $errors = [];
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post  = get_object_vars($request->getPost());
            $padId =  $post['page_analytics_id'];
            $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');

            $form =  $this->getForm();
            $form->setData($post);

            $curData = $table->getEntryById(1)->current();
            if($form->isValid()) {
                if ($curData) {

                    $table->save(array(
                        'pad_current_analytics' => $padId
                    ), $curData->pad_id);

                    // save analytics settings
                    $settingsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsSettingsTable');
                    $settingsData = $settingsTable->getEntryByField('pas_analytics', $padId)->current();


                    if ($settingsData) {
                        $settingsTable->save(array(
                            'pas_analytics' => $padId,
                            'pas_settings' => serialize((array)$request->getPost())
                        ), (int)$settingsData->pas_id);
                    } else {
                        $settingsTable->save(array(
                            'pas_analytics' => $padId,
                            'pas_settings' => serialize((array)$request->getPost())
                        ));
                    }

                    $success = 1;
                    $message = 'tr_meliscms_page_analytics_settings_select_save_ok';
                }
            }
            else {
                $errors = $this->formatErrorMessage($form->getMessages());
            }

        }

        $response = [
            'success' => $success,
            'title'   => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message),
            'errors'  => $errors
        ];


        return new JsonModel($response);

    }

    public function getSettingsFormAction()
    {
        $form = null;
        $view = new ViewModel();

        if ($this->getRequest()->isPost()) {

            // /melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getSettingsForm
            $analyticsKey = $this->getRequest()->getPost('page_analytics_id');
            $config = $this->getServiceLocator()->get('MelisCoreConfig');
            $settings = $config->getItem('meliscms/datas/page_analytics/' . $analyticsKey . '/interface/settings_form');

            if ($settings && $analyticsKey) {
                $factory = new \Zend\Form\Factory();
                $formElements = $this->serviceLocator->get('FormElementManager');

                $factory->setFormElementManager($formElements);
                $form = $factory->createForm($settings);

                $settingsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsSettingsTable');
                $settingsData = $settingsTable->getEntryByField('pas_analytics', $analyticsKey)->current();

                if ($settingsData) {
                    $data = unserialize($settingsData->pas_settings);
                    $form->setData($data);
                }

            }

        }

        $view->form = $form;

        return $view;
    }

    /**
     * Returns the a formatted error messages with its labels
     * @param array $errors
     * @return array
     */
    private function formatErrorMessage($errors = array())
    {
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getItem('meliscms/forms/select_page_analytic_form');
        $appConfigForm = $appConfigForm['elements'];

        foreach ($errors as $keyError => $valueError)
        {
            foreach ($appConfigForm as $keyForm => $valueForm)
            {
                if ($valueForm['spec']['name'] == $keyError &&
                    !empty($valueForm['spec']['options']['label']))
                    $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
            }
        }

        return $errors;
    }

    
    public function toolContentContainerAnalyticsTabAction()
    {
        $melisKey = $this->getMelisKey();
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab');
        
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->hasAccess = $hasAccess;
        return $view;
    }
    public function toolContentContainerAnalyticsTabContentAction()
    {
       $melisKey = $this->getMelisKey();
       $form = $this->getForm();
       $display = null;
        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');

        $curData = (array)$table->getEntryById(1)->current();
        $display = null;

        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_content');
        
        if ($curData) {
            $currentAnalytics = $curData['pad_current_analytics'];
            $data = array('page_analytics_id' => $currentAnalytics);
            $form->setData($data);

            $config = $this->getServiceLocator()->get('MelisCoreConfig');

            if ($currentAnalytics) {
                $pageAnalyticsData = $config->getItem('meliscms/datas/page_analytics');
                $pageAnalyticsData = isset($pageAnalyticsData[$currentAnalytics]) ?
                    $pageAnalyticsData[$currentAnalytics] : null;

                if ($pageAnalyticsData) {
                    $forward = $pageAnalyticsData['forward'];
                    $display = $this->getTool()->getViewContent($forward);
                    $display = str_replace(array(
                        'sDom : "<', 'rip>>"', 'return "<div>',
                        '<endaction/></div>";',
                        '"<a class="btn btn-default melis-refreshTable',
                        'fa-refresh"></i></a>"',
                        '(".search input[type="search"]")'
                    ), array(
                        "sDom : '<", "rip>>'",
                        "return '<div>",
                        "<endaction/></div>';",
                        "'<a class=\"btn btn-default melis-refreshTable",
                        "fa-refresh\"></i></a>'",
                        "(\".search input[type='search']\")"
                    ), $display);
                }

            }
        }
       $view = new ViewModel();

       $view->melisKey = $melisKey;
       $view->display  = $display;
       $view->hasAccess = $hasAccess;

       return $view;

    }



    public function toolContentContainerAnalyticsSettingsTabAction()
    {
        $melisKey = $this->getMelisKey();
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_settings');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->hasAccess = $hasAccess;
        return $view;
    }
    public function toolContentContainerAnalyticsSettingsTabContentAction()
    {

        $melisKey = $this->getMelisKey();
        
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_settings_content');
        $form = $this->getForm();
        
        $view = new ViewModel();
        
        $view->melisKey = $melisKey;
        $view->setVariable('form', $form);
        $view->hasAccess = $hasAccess;
        
        return $view;
    }

    
    /**
     * Checks wether the user has access to this tools or not
     * @return boolean
     */
    private function hasAccess($key)
    {
        $key = trim($key);
        $melisCoreAuth = $this->getServiceLocator()->get('MelisCoreAuth');
        $melisCoreRights = $this->getServiceLocator()->get('MelisCoreRights');
        $xmlRights = $melisCoreAuth->getAuthRights();
        
        $isAccessible = $melisCoreRights->isAccessible($xmlRights, MelisCoreRightsService::MELISCORE_PREFIX_TOOLS, $key);
        
        return $isAccessible;
    }
    /**
     * This method will modify the data of analytics
     *
     * @param siteAnalyticsData
     *
     * return array
     */
    public function modifyAnalyticsData($siteAnalyticsData)
    {
        if($siteAnalyticsData){
            foreach ($siteAnalyticsData as $dataKey => $dataValue){
                if($dataValue == "2015"){
                    $dataValue = "2015";
                }
            }
        }

        return $siteAnalyticsData;

    }


}
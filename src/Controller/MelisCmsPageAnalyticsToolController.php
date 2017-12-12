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
            $post                    = $this->getTool()->sanitizeRecursive(get_object_vars($request->getPost()), array('pads_js_analytics'));
            $analayticsTable         = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
            $analayticsSettingsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataSettingsTable');


            $form = $this->getForm();
            $form->setData($post);

            if ($form->isValid()) {

                $siteId       = (int) $post['pad_site_id'];
                $analyticsKey = $post['pad_analytics_key'];

                // create a temp storage for serialization excluding the important columns
                $tmpPostSrlz = $post;
                unset($tmpPostSrlz['pad_site_id']);
                unset($tmpPostSrlz['pad_analytics_key']);
                unset($tmpPostSrlz['pads_js_analytics']);

                // Automatically add 'ga:' prefix on the view ID
                if ($analyticsKey === 'melis_cms_google_analytics'){
                    if (mb_substr($tmpPostSrlz['google_analytics_view_id'], 0, 3) !== 'ga:')
                        $tmpPostSrlz['google_analytics_view_id'] = 'ga:'.$tmpPostSrlz['google_analytics_view_id'];
                }

                $analyticsSettings = serialize($tmpPostSrlz);

                // first check if the analytics data exists
                $analyticsData = $analayticsTable->getEntryByField('pad_site_id', $siteId)->current();

                if($analyticsData) {
                    // update analytics data table to set what analytics key is currently being selected
                    $analyticsId = $analayticsTable->save(array(
                        'pad_analytics_key' => $post['pad_analytics_key']
                    ), $analyticsData->pad_id);
                }
                else {
                    $analyticsId = $analayticsTable->save(array(
                        'pad_site_id' => $siteId,
                        'pad_analytics_key' => $post['pad_analytics_key']
                    ));
                }

                // check if the analytics settings data exists
                $analyticsSettingsData = $analayticsTable->getAnalytics($siteId, $analyticsKey)->current();

                if($analyticsSettingsData && $analyticsSettingsData->pads_id) {

                    // removes js script if analytics module selected is NOT google analytics
                    if($post['pad_analytics_key'] !== 'melis_cms_google_analytics'){
                        $post['pads_js_analytics'] = '';
                    }

                    // update the analytics settings data
                    $analayticsSettingsTable->save(array(
                        'pads_settings' => $analyticsSettings,
                        'pads_js_analytics' => $post['pads_js_analytics']
                    ), $analyticsSettingsData->pads_id);
                }
                else {
                    // New table entry
                    $analayticsSettingsTable->save(array(
                        'pads_site_id' => $siteId,
                        'pads_analytics_key' =>  $analyticsKey,
                        'pads_settings' => $analyticsSettings,
                        'pads_js_analytics' => $post['pads_js_analytics']
                    ));
                }


                if($analyticsId) {
                    $success = 1;
                    $message = 'tr_meliscms_page_analytics_settings_select_save_ok';
                }


            } else {
                $errors = $this->formatErrorMessage($form->getMessages());
            }

        }

        $response = [
            'success' => $success,
            'title' => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors
        ];


        return new JsonModel($response);

    }

    public function getSettingsFormAction()
    {
        $form = null;
        $view = new ViewModel();

        if ($this->getRequest()->isPost()) {
            $analyticsKey = $this->getTool()->sanitize($this->getRequest()->getPost('analytics_key'));
            $siteId       = (int) $this->getRequest()->getPost('site_id');
            $config = $this->getServiceLocator()->get('MelisCoreConfig');
            $settings = $config->getItem('meliscms/datas/page_analytics/' . $analyticsKey . '/interface/settings_form');

            if ($settings && $analyticsKey) {
                $factory = new \Zend\Form\Factory();
                $formElements = $this->serviceLocator->get('FormElementManager');

                $factory->setFormElementManager($formElements);
                $form = $factory->createForm($settings);

                $analyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
                $settingsData = $analyticsTable->getAnalytics($siteId, $analyticsKey)->current();

                if ($settingsData) {
                    $data = unserialize($settingsData->pads_settings);
                    $data['google_analytics_view_id'] = substr($data['google_analytics_view_id'], 3);
                    $form->setData($data);
                }

            }

        }

        $view->form = $form;

        return $view;
    }

    public function getSiteAnalyticsAction()
    {
        $success = false;
        $errors = array();
        $data = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $analyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
            $siteId = (int)$request->getPost('site_id');
            $analyticsData = $analyticsTable->getAnalytics($siteId)->current();

            if ($analyticsData) {
                $currentAnalyticsKey = $analyticsData->pad_analytics_key;
                $analyticsData = $analyticsTable->getAnalytics($siteId, $currentAnalyticsKey)->current();

                if($analyticsData) {
                    $data['page_analytics_id'] = $analyticsData->pad_analytics_key;
                    $data['pads_js_analytics'] = $analyticsData->pads_js_analytics;
                }

            }
        }

        $response = array(
            'success' => $success,
            'errors' => $errors,
            'response' => $data
        );


        return new JsonModel($response);
    }

    public function getAnalyticsScriptAction()
    {
        $success = 0;
        $data    = array();
        $request = $this->getRequest();

        if($request->isPost()) {

            $siteId = (int) $request->getPost('site_id');
            $analyticsKey = $this->getTool()->sanitize($request->getPost('analytics_key'));

            $analyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
            $analyticsData = $analyticsTable->getAnalytics($siteId, $analyticsKey)->current();

            if($analyticsData)
                $data['pads_analytics_key'] = $analyticsData->pads_analytics_key;
            $data['pads_js_analytics']  = $analyticsData->pads_js_analytics;

            $success = 1;
        }

        $response = array(
            'success'  => $success,
            'response' => $data
        );

        return new JsonModel($response);
    }


    public function toolContentContainerAnalyticsTabContentAction()
    {
        $melisKey = $this->getMelisKey();
        $form = $this->getForm();
        $display = null;
        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');

        $siteId  = (int) $this->params()->fromQuery('siteId', null);
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_content');
        $curData = array();
        $errMsg  = "";

        if($siteId) {
            $curData = $table->getEntryByField('pad_site_id', $siteId)->current();


            if ($curData) {
                $config = $this->getServiceLocator()->get('MelisCoreConfig');
                $currentAnalytics = $curData->pad_analytics_key;
                $hasAnalyticsConfig = $config->getItem('meliscms/datas/page_analytics/'.$currentAnalytics);

                if($hasAnalyticsConfig) {
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
                else {
                    $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_module_deactivated_msg');
                }


            }
        }

        $view = new ViewModel();

        $view->melisKey  = $melisKey;
        $view->display   = $display;
        $view->hasAccess = $hasAccess;
        $view->siteId    =  $siteId;
        $view->errMsg    =  $errMsg;

        $view->setVariable('form', $form);

        return $view;

    }


    public function toolContentContainerAnalyticsSettingsTabContentAction()
    {

        $melisKey = $this->getMelisKey();

        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_settings_content');
        $form = $this->getForm();

        $view = new ViewModel();

        $view->google_analytics_config_guide = $this->getGoogleAnalyticsGuideAction();
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
     * Returns the a formatted error messages with its labels
     * @param array $errors
     * @return array
     */
    private function formatErrorMessage($errors = array())
    {
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getItem('meliscms/forms/select_page_analytic_form');
        $appConfigForm = $appConfigForm['elements'];

        foreach ($errors as $keyError => $valueError) {
            foreach ($appConfigForm as $keyForm => $valueForm) {
                if ($valueForm['spec']['name'] == $keyError &&
                    !empty($valueForm['spec']['options']['label'])
                )
                    $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
            }
        }

        return $errors;
    }

    private function getGoogleAnalyticsGuideAction()
    {
        $guide  = '';
        $tool   = $this->getTool();
        $guide  = '<h4>'.$tool->getTranslation('tr_meliscms_google_analytics_guide_title').'</h4>';
        $guide .= '<p><strong>'.$tool->getTranslation('tr_meliscms_google_analytics_guide_subtitle').'</strong></p>';

        $step   = 0;
        for ($i=1; $i < 4; $i++) {
            $guide .= '<p><strong>'. $tool->getTranslation('tr_meliscms_google_analytics_guide_step'.$i) . '</strong></p>';
            //$steps = explode(PHP_EOL, $tool->getTranslation('tr_meliscms_google_analytics_guide_step'.$i.'_items'));
            $guide .= '<p><ol>';
            $step   = 1;
            while (substr($tool->getTranslation('tr_meliscms_google_analytics_guide_step'.$i.'_item'.$step), 0, 28) !== 'tr_meliscms_google_analytics'){
                $guide .= '<li>'.$tool->getTranslation('tr_meliscms_google_analytics_guide_step'.$i.'_item'.$step).'</li>';
                $step++;
            }
            $guide .= '</ol></p>';
        }

        $guide .= $tool->getTranslation('tr_meliscms_google_analytics_guide_footnote');

        return $guide;
    }
}
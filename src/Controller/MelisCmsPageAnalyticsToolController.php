<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalytics\Controller;

use MelisCore\Service\MelisCoreRightsService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class MelisCmsPageAnalyticsToolController extends AbstractActionController
{
    const DS = DIRECTORY_SEPARATOR;

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

    public function toolHeaderContainerAction()
    {
        $melisKey = $this->getMelisKey();
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        $view->title = $this->getTool()->getTranslation('tr_meliscms_page_analytics_title');

        return $view;
    }

    private function getTool()
    {
        $toolSvc = $this->getServiceLocator()->get('MelisCoreTool');
        $toolSvc->setMelisToolKey('MelisCmsPageAnalytics', 'MelisCmsPageAnalytics_tool');
        return $toolSvc;
    }

    //Tool HeaderContainer Action

    public function toolContentTableLimitAction()
    {
        return new ViewModel();
    }

    /*
     * Limit data in the table
     */

    public function toolContentTableSearchToolAction()
    {
        return new ViewModel();
    }

    /*
     * Search Data in the table
     */

    public function toolContentTableActionViewAction()
    {
        return new ViewModel();
    }

    /*
    * View Details of data in the table
    */

    public function toolContentTableRefreshToolAction()
    {
        return new ViewModel();
    }

    /*
     * Zone Refresh
     */

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

        $isAccessible = $melisCoreRights->isAccessible($xmlRights, MelisCoreRightsService::MELIS_PLATFORM_TOOLS_PREFIX, $key);

        return $isAccessible;
    }

    public function toolDefaultPageAnalyticsTableAction()
    {
        $melisKey = $this->getMelisKey();
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab');
        $columns = $this->getTool()->getColumns();

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->tableColumns = $columns;
        $view->hasAccess = $hasAccess;

        // Setting first column's (ID) default Order to descending
        $view->getToolDataTableConfig = $this->getTool()->getDataTableConfiguration('#tableMelisCmsPageAnalytics', true, false, array('order' => '[[0, "desc"]]'));

        return $view;
    }


    public function getMelisCmsPageAnalyticsDataAction()
    {
        $request = $this->getRequest();

        $dataCount = 0;
        $dataFilteredCount = 0;
        $tableData = array();
        $draw = 0;

        if ($request->isPost()) {

            $post = $request->getPost()->toArray();
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

                // Marking deleted pages
                if (empty($tableData[$ctr]['page_name'])) {
                    $tableData[$ctr]['page_name'] = $this->getTool()->getTranslation('tr_meliscms_page_analytics_site_analytics_deleted_marker');
                }
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

    private function melisCmsPageAnalytcisTable()
    {
        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsTable');
        return $table;
    }

    public function saveAction()
    {
        $success = 0;
        $title = 'tr_meliscms_page_analytics_title';
        $message = 'tr_meliscms_page_analytics_settings_select_save_ko';
        $errors = [];
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $this->getTool()->sanitizeRecursive($request->getPost()->toArray(), array('pads_js_analytics'));

            /**
             * Merging the files info
             */
            $post = array_merge_recursive(
                $post,
                $request->getFiles()->toArray()
            );

            $analayticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
            $analayticsSettingsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataSettingsTable');

            /**
             * Checking for google analytics settings form errors.
             * Disclaimer: This is not the ideal way of doing things but MelisCmsPageAnalytics module
             * was designed this way. A possible refactor in the future can be done.
             */
            if ($post['pad_analytics_key'] == 'melis_cms_google_analytics') {
                $googleAnalytics = $this->getGoogleAnalyticsService();
                $form = $googleAnalytics->getSettingsForm();
                $form->setData($post);
                if (!$form->isValid()) {
                    $errors = $this->formatErrorMessage($form->getMessages(), $post['pad_analytics_key']);
                }
            }

            $form = $this->getForm();
            $form->setData($post);

            if ($form->isValid() && empty($errors)) {
                $formData = $form->getData();
                $siteId = (int)$formData['pad_site_id'];
                $analyticsKey = $formData['pad_analytics_key'];
                $fileChanged = empty($post['fileChanged']) ? false : ($post['fileChanged'] === "false" ? false : true);
                $analyticsSettings = [];
                $analyticsSettingsData = $analayticsTable->getAnalytics($siteId, $analyticsKey)->current();
                $privateKeyFileDir = '';

                if ($analyticsKey == 'melis_cms_google_analytics') {
                    /**
                     * Storing the private key file to the private key file directory.
                     * > User changed the Private Key file
                     * > Uploaded private key is NOT empty
                     * > Uploaded private key is JSON
                     *
                     * Additional validations can be added in the future such as:
                     * checking for certain keys, format of the values, etc.
                     */
                    if ($fileChanged === true &&
                        !empty($post['google_analytics_private_key']) &&
                        $post['google_analytics_private_key']['type'] === "application/json"
                    ) {
                        $privateKey = $post['google_analytics_private_key'];
                        $conf = $this->getServiceLocator()->get('MelisCoreConfig')->getItem('meliscms');
                        if (!empty($conf['datas']['page_analytics']['melis_cms_google_analytics']['datas']['private_key_file_directory'])) {
                            $privateKeyFileDir = $conf['datas']['page_analytics']['melis_cms_google_analytics']['datas']['private_key_file_directory'];
                            $privateKeyFileDir = str_replace(["\\", "/"], self::DS, $privateKeyFileDir);
                        }
                        $src = $privateKey['tmp_name'];
                        $dst = __DIR__ . self::DS . '..' . self::DS . '..' . self::DS . '..' . $privateKeyFileDir;
                        /**
                         * Check the directory & throw error if directory does not exist.
                         */
                        if (is_writable($dst)) {
                            if (file_exists($dst) && is_dir($dst)) {
                                $dst .= self::DS . $privateKey['name'];
                                copy($src, $dst);
                            } else {
                                $errors['no_perms'] = 'Private key file directory does not exist.';
                            }
                        } else {
                            $errors['no_perms'] = 'Contact administrator to set proper permissions to the directory.';
                        }
                        /**
                         *  Prepare settings to be serialized
                         */
                        $analyticsSettings['google_analytics_private_key'] = realpath($dst);
                    } else {
                        /** retain the current private key file */
                        $currentSetting = unserialize($analyticsSettingsData->pads_settings);
                        $analyticsSettings['google_analytics_private_key'] = $currentSetting['google_analytics_private_key'];
                    }
                    $analyticsSettings['google_analytics_view_id'] = 'ga:' . $post['google_analytics_view_id'];
                }

                $analyticsSettings = serialize($analyticsSettings);

                /** first check if the analytics data exists */
                $analyticsData = $analayticsTable->getEntryByField('pad_site_id', $siteId)->current();

                if ($analyticsData) {
                    // update analytics data table to set what analytics key is currently being selected
                    $analyticsId = $analayticsTable->save(array(
                        'pad_analytics_key' => $analyticsKey
                    ), $analyticsData->pad_id);
                } else {
                    $analyticsId = $analayticsTable->save(array(
                        'pad_site_id' => $siteId,
                        'pad_analytics_key' => $analyticsKey
                    ));
                }

                /** check if the analytics settings data exists */
                if (!empty($analyticsSettingsData) && !empty($analyticsSettingsData->pads_id)) {
                    // removes js script if analytics module selected is NOT google analytics
                    if ($analyticsKey !== 'melis_cms_google_analytics') {
                        $post['pads_js_analytics'] = '';
                    }

                    // update the analytics settings data
                    $analayticsSettingsTable->save(array(
                        'pads_settings' => $analyticsSettings,
                        'pads_js_analytics' => $post['pads_js_analytics']
                    ), $analyticsSettingsData->pads_id);
                } else {
                    // New table entry
                    $analayticsSettingsTable->save(array(
                        'pads_site_id' => $siteId,
                        'pads_analytics_key' => $analyticsKey,
                        'pads_settings' => $analyticsSettings,
                        'pads_js_analytics' => $post['pads_js_analytics']
                    ));
                }

                if ($analyticsId) {
                    $success = 1;
                    $message = 'tr_meliscms_page_analytics_settings_select_save_ok';
                }
            } else {
                $errors = array_merge($errors, $this->formatErrorMessage($form->getMessages(), $post['pad_analytics_key']));
            }

        }

        $response = [
            'success' => $success,
            'title' => $this->getTool()->getTranslation($title),
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors
        ];

        // add to flash messenger
        $this->getEventManager()->trigger('melis_cms_page_analytics_flash_messenger', $this, $response);

        return new JsonModel($response);

    }

    /**
     * Returns the Google Analytics Service if it exists,
     * otherwise, returns false.
     * @return array|bool|object
     */
    private
    function getGoogleAnalyticsService()
    {
        try {
            $service = $this->getServiceLocator()->get('MelisCmsGoogleAnalyticsService');
            return $service;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Returns the a formatted error messages with its labels
     * @param array $errors
     * @param $analyticsModule
     * @return array
     */
    private
    function formatErrorMessage($errors = array(), $analyticsModule)
    {
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getItem('meliscms/forms/' . $analyticsModule . '_settings_form');
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

    private function getForm()
    {
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $formConfig = $config->getItem('meliscms/forms/melis_cms_page_analytics_settings_form');
        $factory = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');

        $factory->setFormElementManager($formElements);

        $form = $factory->createForm($formConfig);

        return $form;
    }

    /**
     * Returns the settings form of the selected analytics module for a site
     *
     * Disclaimer:
     *  This is not the ideal way of extending a module's functionality,
     *  a possible refactor can be done in the future to improve this method
     *
     * @return ViewModel
     */
    public
    function getSettingsFormAction()
    {
        $form = null;
        $view = new ViewModel();

        if ($this->getRequest()->isPost()) {
            $analyticsKey = $this->getTool()->sanitize($this->getRequest()->getPost('analytics_key'));
            $siteId = (int)$this->getRequest()->getPost('site_id');
            $config = $this->getServiceLocator()->get('MelisCoreConfig');
            $settings = $config->getItem('meliscms/forms/' . $analyticsKey . '_settings_form');

            if (!empty($settings) && !empty($analyticsKey)) {
                $factory = new \Zend\Form\Factory();
                $formElements = $this->serviceLocator->get('FormElementManager');
                $factory->setFormElementManager($formElements);
                /** @var \Zend\Form\Form $form */
                $form = $factory->createForm($settings);

                /**
                 * Checking for a writable private key directory,
                 * otherwise disable the private key upload/browsing function
                 */
                if ($analyticsKey === 'melis_cms_google_analytics') {
                    $privateKeyDir = $config->getItem('meliscms/datas/page_analytics/' . $analyticsKey . '/datas');
                    if (!empty($privateKeyDir['private_key_file_directory'])) {
                        $privateKeyDir = $privateKeyDir['private_key_file_directory'];
                        // Replace proper directory separators (either windows / linux)
                        $privateKeyDir = str_replace(['\\', '/'], self::DS, $privateKeyDir);
                        $privateKeyDir = realpath(__DIR__ . self::DS . '..' . self::DS . '..' . self::DS . '..' . $privateKeyDir);

                        if (empty($privateKeyDir) || !file_exists($privateKeyDir) || !is_writable($privateKeyDir)) {
                            $privateKeyDirElement = $form->get('google_analytics_private_key')->setAttribute('disabled', 'disabled');
                            $form->add($privateKeyDirElement);
                        }
                    }
                }

                $analyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
                $settingsData = $analyticsTable->getAnalytics($siteId, $analyticsKey)->current();

                if (!empty($settingsData)) {
                    $data = unserialize($settingsData->pads_settings);
                    if (!empty($data['google_analytics_view_id']) && strpos($data['google_analytics_view_id'], 'ga:') !== false) {
                        $viewIdStart = (int)strpos($data['google_analytics_view_id'], 'ga:');
                        $data['google_analytics_view_id'] = substr($data['google_analytics_view_id'], $viewIdStart + 3);
                    } else {
                        $data = [];
                    }

                    /**
                     * Get the file name to act as a placeholder for the browse button
                     */
                    if (!empty($data['google_analytics_private_key'])) {
                        $privateKeyFileName = explode(self::DS, $data['google_analytics_private_key']);
                        $data['google_analytics_private_key_val'] = $privateKeyFileName[count($privateKeyFileName) - 1];

                        /**
                         * Creating the element that will hold the private key filename value
                         */
                        $form->add([
                            'type' => 'Zend\\Form\\Element\\Hidden',
                            'name' => 'google_analytics_private_key_val',
                            'attributes' => [
                                'id' => 'id_google_analytics_private_key_val',
                                'value' => $privateKeyFileName
                            ]
                        ]);
                    }

                    $form->setData($data);
                }
            } else {
                echo "No settings form was found from the configuration files.";
            }
        }
        $view->form = $form;

        return $view;
    }

    public
    function getSiteAnalyticsAction()
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
                $data['analyticsModuleIsSet'] = true;
                $currentAnalyticsKey = $analyticsData->pad_analytics_key;
                $currentAnalyticsData = $analyticsTable->getAnalytics($siteId, $currentAnalyticsKey)->current();

                if ($currentAnalyticsData) {
                    $data['page_analytics_id'] = $currentAnalyticsData->pad_analytics_key;
                    $success = true;

                    // Checks if Current Analytics is activated
                    if ($data['page_analytics_id'] == 'melis_cms_no_analytics') {
                        // active by default
                        $data['activeAnalytics'] = true;
                    } else {
                        $config = $this->getServiceLocator()->get('MelisCoreConfig');
                        $data['activeAnalytics'] = $config->getItem('meliscms/datas/page_analytics/' . $data['page_analytics_id']) ? true : false;
                    }

                }
            } else $data['analyticsModuleIsSet'] = false;
        }

        $response = array(
            'success' => $success,
            'errors' => $errors,
            'response' => $data,
        );


        return new JsonModel($response);
    }

    public
    function getAnalyticsScriptAction()
    {
        $success = 0;
        $data = array();
        $request = $this->getRequest();

        if ($request->isPost()) {

            $siteId = (int)$request->getPost('site_id');
            $analyticsKey = $this->getTool()->sanitize($request->getPost('analytics_key'));

            $analyticsTable = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');
            $analyticsData = $analyticsTable->getAnalytics($siteId, $analyticsKey)->current();

            if ($analyticsData) {
                $data['pads_analytics_key'] = $analyticsData->pads_analytics_key;
            }
            $data['pads_js_analytics'] = $analyticsData->pads_js_analytics ?? '';

            $success = 1;
        }

        $response = array(
            'success' => $success,
            'response' => $data
        );

        return new JsonModel($response);
    }

    /**
     * Returns the Contents of the selected Page Analytics Module
     * @return ViewModel
     */
    public
    function toolContentContainerAnalyticsTabContentAction()
    {
        $melisKey = $this->getMelisKey();
        $form = $this->getForm();
        $display = null;
        $table = $this->getServiceLocator()->get('MelisCmsPageAnalyticsDataTable');

        $siteId = (int)$this->params()->fromQuery('siteId', null);
        $hasAccess = $this->hasAccess('meliscms_page_analytics_site_analytics_tab_content');
        $curData = array();
        $errMsg = "";

        if (!empty($siteId)) {
            /**
             * Getting site analytics data of the current Site selected
             */
            $curData = $table->getEntryByField('pad_site_id', $siteId)->current();

            if (!empty($curData)) {
                /**
                 * Checking for analytics configuration
                 */
                $config = $this->getServiceLocator()->get('MelisCoreConfig');
                $currentAnalytics = $curData->pad_analytics_key;
                $hasAnalyticsConfig = $config->getItem('meliscms/datas/page_analytics/' . $currentAnalytics);

                if (!empty($hasAnalyticsConfig)) {
                    /**
                     * Pre-selecting the analytics module in the drop-down menu for this site
                     */
                    $data = array('page_analytics_id' => $currentAnalytics);
                    $form->setData($data);
                    $config = $this->getServiceLocator()->get('MelisCoreConfig');

                    if (!empty($currentAnalytics)) {
                        /**
                         * Getting the display (config, forward, interface, etc.) for the selected analytics module
                         */
                        $pageAnalyticsData = $config->getItem('meliscms/datas/page_analytics');
                        $pageAnalyticsData = isset($pageAnalyticsData[$currentAnalytics]) ?
                            $pageAnalyticsData[$currentAnalytics] : null;

                        if (!empty($pageAnalyticsData)) {
                            $forward = $pageAnalyticsData['forward'];
                            $display = $this->getTool()->getViewContent($forward);
                            $display = str_replace(array(
                                'sDom : "<', 'rip>>"', 'return "<div>',
                                '<endaction/></div>";',
                                '"<a class="btn btn-default melis-cms-page-analytics-refresh-table-tool',
                                'fa-refresh"></i></a>"',
                                '(".melis_cms_page_analytics_tool_search input[type="search"]")'
                            ), array(
                                "sDom : '<", "rip>>'",
                                "return '<div>",
                                "<endaction/></div>';",
                                "'<a class=\"btn btn-default melis-cms-page-analytics-refresh-table-tool",
                                "fa-refresh\"></i></a>'",
                                "(\".melis_cms_page_analytics_tool_search input[type='search']\")"
                            ), $display);
                        }
                    }
                } else {
                    $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_inactive_module');
                }
            } else {
                $errMsg = $this->getTool()->getTranslation('tr_meliscms_page_analytics_no_module_set');
            }
        }

        $view = new ViewModel();

        $view->melisKey = $melisKey;
        $view->display = $display;
        $view->hasAccess = $hasAccess;
        $view->siteId = $siteId;
        $view->errMsg = $errMsg;
        $view->form = $form;

        return $view;

    }

    public
    function toolContentContainerAnalyticsSettingsTabContentAction()
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
     * Gets the guidelines on how to setup Google Analytics
     * via Google Analytics Service
     * @return string
     */
    private
    function getGoogleAnalyticsGuideAction()
    {
        $guide = '';
        $googleAnalytics = $this->getGoogleAnalyticsService();

        if ($googleAnalytics) {
            // google-analytics-service exists
            $guide = $googleAnalytics->getGoogleAnalyticsGuide();
        }

        return $guide;
    }
}

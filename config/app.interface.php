<?php
namespace MelisCmsPageAnalytics;
return array(
    'plugins' => array(
        'meliscore' => array(
            'interface' => array(
                'meliscore_leftmenu' => array(
                    'interface' => array(
                        'melismarketing_toolstree_section' => array(
                            'interface' => array(
                                'meliscms_site_tools_parent_menu' => array(
                                    'conf' => [
                                        'name' => 'tr_meliscms_page_analytics_title',
                                        'icon' => 'fa-bar-chart',
                                        'melisKey' => 'meliscms_site_tools_parent_menu'
                                    ],
                                    'interface' => array(
                                        // this will be the configuration of the tool.
                                        'meliscms_page_analytics_tool_config' => array(
                                            'conf' => array(
                                                'type' => '/meliscms_page_analytics_tool_config/interface/meliscms_page_analytics_tool_display',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'MelisCmsPageAnalytics' => array(
            'conf' => array(
                'rightsDisplay' => 'none'
            ),
        ),
        //Add tab Page Analytics in page Preview
        'meliscms' => array(
            'datas' => array(
                'page_analytics' => array(
                    /**
                     * This is the main display of your page analytics tool.
                     */
                    'melis_cms_page_analytics' => array(
                        'conf' => array(
                            'id'        => 'melis_cms_page_analytics',
                            'name'      => 'tr_melis_cms_page_analytics',
                            'melisKey'  => 'melis_cms_page_analytics',
                        ),
                        'forward' => array(
                            'module'     => 'MelisCmsPageAnalytics',
                            'controller' => 'MelisCmsPageAnalyticsTool',
                            'action'     => 'tool-default-page-analytics-table',
                        ),
                        'interface' => array(
                            /**
                             * The display in CMS pages analytics tab
                             */
                            'analytics_for_page' => array(
                                'conf' => array(
                                    'id'        => 'melis_cms_page_analytics',
                                    'name'      => 'tr_melis_cms_page_analytics',
                                    'melisKey'  => 'melis_cms_page_analytics',
                                ),
                                'forward' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action'     => 'tool-content-container',
                                ),
                            ),
                        ),
                        'datas' => array(
                            'settings_form_path' => '',
                        ),
                    )
                )
            ),
            'interface' => array(
                'meliscms_page' => array(
                    'interface'=> array(
                        'meliscms_tabs' => array(
                            'interface' => array(
                                'meliscms_page_analytics_tab' => array(
                                    'conf' => array(
                                        'id' => 'id_meliscms_page_analytics',
                                        'name' => 'tr_melis_cms_page_analytics_title',
                                        'icon' => 'stats'
                                    ),
                                    'interface' => array(
                                        'meliscms_page_analytics_tab_display' => array(
                                            'conf'  => array(
                                                'id'    => 'id_meliscms_page_analytics',
                                                'name'  => 'tr_meliscms_page_analytics_tab_display',
                                                'melisKey'  => 'meliscms_page_analytics_tab_display',
                                            ),
                                            'forward' => array(
                                                'module' => 'MelisCmsPageAnalytics',
                                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                                'action' => 'tool-container',
                                                'jscallback' => '',
                                                'jsdatas' => array()
                                            ),
                                            'interface' => array(
                                                'meliscms_page_analytics_tab_header' => array(
                                                    'conf' => array(
                                                        'id'    => 'id_meliscms_page_analytics_tab_header',
                                                        'name'  => 'tr_meliscms_page_analytics_tab_header',
                                                        'melisKey'  => 'meliscms_page_analytics_tab_header',
                                                    ),
                                                    'forward' => array(
                                                        'module' => 'MelisCmsPageAnalytics',
                                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                                        'action' => 'tool-header-container',
                                                        'jscallback' => '',
                                                        'jsdatas' => array()
                                                    ),
                                                ),
                                                // For reloading table in the page
                                                'melis_cms_page_analytics_page_table' => array(
                                                    'conf' =>array(
                                                        'id' => 'id_melis_cms_page_analytics_page_table',
                                                        'name' => 'tr_melis_cms_page_analytics_page_table',
                                                        'melisKey' => 'melis_cms_page_analytics_page_table'
                                                    ),
                                                    'forward' => array(
                                                        'module' => 'MelisCmsPageAnalytics',
                                                        'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                                        'action' => 'tool-content-container'
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        /**
         * this is the configuration of the tool
         */
        'meliscms_page_analytics_tool_config' => array(
            'conf' => array(
                'id'   => 'id_meliscms_page_analytics_tool_config',
                'name' => 'tr_meliscms_page_analytics_tool_config',
                'melisKey' => 'meliscms_page_analytics_tool_config',
                'rightsDisplay' => 'none'
            ),
            'ressources' => array(
                'css' => array(
                    '/MelisCmsPageAnalytics/css/site_analytics.css',
                ),
                'js' => array(
                    '/MelisCmsPageAnalytics/plugin/ace-editor/ace.js',
                    '/MelisCmsPageAnalytics/plugin/ace-editor/ext-old_ie.js',
                    '/MelisCmsPageAnalytics/js/pagehit.tool.js',
                    '/MelisCmsPageAnalytics/js/page_analytics.tool.js',
                ),
                /**
                 * the "build" configuration compiles all assets into one file to make
                 * lesser requests
                 */
                'build' => [
                    // lists of assets that will be loaded in the layout
                    'css' => [
                        '/MelisCmsPageAnalytics/build/css/bundle.css',

                    ],
                    'js' => [
                        '/MelisCmsPageAnalytics/build/js/bundle.js',
                    ]
                ]
            ),
            'interface' => array(
                'meliscms_page_analytics_tool_display' => array(
                    'conf' => array(
                        'id'   => 'id_meliscms_page_analytics_display',
                        'name' => 'tr_meliscms_page_analytics_tool_display_title',
                        'melisKey' => 'meliscms_page_analytics_display',
                        'icon' => 'fa-bar-chart',
                        'rights_checkbox_disable' => true
                    ),
                    'forward' => array(
                        'module' => 'MelisCmsPageAnalytics',
                        'controller' => 'MelisCmsPageAnalyticsTool',
                        'action' => 'tool-container',
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                    'interface' => array(
                        'meliscms_page_analytics_header' => array(
                            'conf' => array(
                                'id'   => 'id_meliscms_page_analytics_header',
                                'name' => 'tr_meliscms_page_analytics_header',
                                'melisKey' => 'meliscms_page_analytics_header',
                            ),
                            'forward' => array(
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsTool',
                                'action' => 'tool-header-container',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        ),
                        'meliscms_page_analytics_content' => array(
                            'conf' => array(
                                'id'   => 'id_meliscms_page_analytics_content',
                                'name' => 'tr_meliscms_page_analytics_content',
                                'melisKey' => 'meliscms_page_analytics_content',
                            ),
                            'forward' => array(
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsTool',
                                'action' => 'tool-content-container',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                            'interface' => array(
                                // tab content
                                'meliscms_page_analytics_site_analytics_tab_content' => array(
                                    'conf' => array(
                                        'id'   => 'id_meliscms_page_analytics_site_analytics_tab_content',
                                        'name' => 'tr_meliscms_page_analytics_tab_content',
                                        'melisKey' => 'meliscms_page_analytics_site_analytics_tab_content',
                                        'icon' => 'glyphicons stats'
                                    ),
                                    'forward' => array(
                                        'module' => 'MelisCmsPageAnalytics',
                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                        'action' => 'tool-content-container-analytics-tab-content',
                                        'jscallback' => '',
                                        'jsdatas' => array()
                                    ),
                                    'interface' => array(
                                        // added to have zone reload only the tab
                                        'melis_cms_page_analytics_tool_table' => array(
                                            'conf' =>array(
                                                'id' => 'id_melis_cms_page_analytics_tool_table',
                                                'name' => 'tr_melis_cms_page_analytics_tool_table',
                                                'melisKey' => 'melis_cms_page_analytics_tool_table'
                                            ),
                                            'forward' => array(
                                                'module' => 'MelisCmsPageAnalytics',
                                                'controller' => 'MelisCmsPageAnalyticsTool',
                                                'action' => 'tool-default-page-analytics-table'
                                            ),
                                        ),
                                    ),
                                ),
                                'meliscms_page_analytics_site_analytics_tab_settings_content' => array(
                                    'conf' => array(
                                        'id'   => 'id_meliscms_page_analytics_site_analytics_tab_settings_content',
                                        'name' => 'tr_meliscms_page_analytics_settings_tab_content',
                                        'melisKey' => 'meliscms_page_analytics_site_analytics_tab_settings_content',
                                        'icon' => 'glyphicons  settings'

                                    ),
                                    'forward' => array(
                                        'module' => 'MelisCmsPageAnalytics',
                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                        'action' => 'tool-content-container-analytics-settings-tab-content',
                                        'jscallback' => '',
                                        'jsdatas' => array()
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'meliscms_page_analytics_page_details' => array(
                    'conf' => array(
                        'id'   => 'id_meliscms_page_analytics_page_details',
                        'name' => 'tr_meliscms_page_analytics_page_detail_title',
                        'melisKey' => 'meliscms_page_analytics_page_details',
                    ),
                    'forward' => array(
                        'module' => 'MelisCmsPageAnalytics',
                        'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                        'action'     => 'tool-container-page-analytics-page-details',
                        'jscallback' => '',
                        'jsdatas'    => array()
                    ),
                    'interface'   => array(
                        'meliscms_page_analytics_page_details_header' => array(
                            'conf' => array(
                                'id' => 'id_meliscms_page_analytics_page_details_header',
                                'name' => 'tr_meliscms_page_analytics_page_details_title',
                                'melisKey' => 'meliscms_page_analytics_page_details_header',
                            ),
                            'forward' => array(
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                'action' => 'tool-header-container',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        ),
                        'meliscms_page_analytics_page_details_content' => array(
                            'conf' => array(
                                'id' => 'id_meliscms_page_analytics_page_details_content',
                                'name' => 'tr_meliscms_page_analytics_page_details_content_title',
                                'melisKey' => 'meliscms_page_analytics_page_details_content',
                            ),
                            'forward' => array(
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                'action' => 'tool-content-container',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);

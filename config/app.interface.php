<?php
namespace MelisCmsPageAnalytics;
return [
    'plugins' => [
        'meliscore' => [
            'interface' => [
                'meliscore_leftmenu' => [
                    'interface' => [
                        'melismarketing_toolstree_section' => [
                            'interface' => [
                                'meliscms_site_tools_parent_menu' => [
                                    'conf' => [
                                        'name' => 'tr_meliscms_page_analytics_title',
                                        'icon' => 'fa-bar-chart',
                                        'melisKey' => 'meliscms_site_tools_parent_menu'
                                    ],
                                    'interface' => [
                                        // this will be the configuration of the tool.
                                        // Rights-bearing node of the Page Analytics menu entry, shaped
                                        // like MelisCmsSlider / MelisCmsNews / MelisCmsSiteRobot.
                                        //
                                        // It used to carry ONLY the `conf.type` link, so the key it
                                        // resolved to (`meliscms_page_analytics_display`) was declared on
                                        // the target, in the `meliscms_page_analytics_tool_config` plugin
                                        // root. A wrapper is inferred via configIsParentOf, which matches
                                        // it against SEGMENTS OF THE GRANTED TOOL'S CONFIG PATH — and that
                                        // target path never runs through `meliscms_site_tools_parent_menu`,
                                        // so the wrapper stayed denied and TreeToolsController:73 dropped
                                        // the subtree: invisible in the legacy menu for non-admins even
                                        // when granted (admin bypass hides it), while React showed it.
                                        //
                                        // Declaring the melisKey HERE puts the rights key back on the
                                        // left-menu path, which does run through the wrapper. Array key ==
                                        // melisKey so getSectionParent resolves it (else a legacy save
                                        // files the grant under <noparent>). The `conf.type` link still
                                        // pulls in the target's forward, and the target keeps its own
                                        // melisKey as the renderable ZONE key.
                                        'meliscms_page_analytics_tools_section' => [
                                            'conf' => [
                                                'id'   => 'id_meliscms_page_analytics_tools_section',
                                                'name' => 'tr_meliscms_page_analytics_tool_display_title',
                                                'icon' => 'fa-bar-chart',
                                                'rights_checkbox_disable' => false,
                                                'melisKey' => 'meliscms_page_analytics_tools_section',
                                                'type' => '/meliscms_page_analytics_tool_config/interface/meliscms_page_analytics_tool_display',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'MelisCmsPageAnalytics' => [
            'conf' => [
                'rightsDisplay' => 'none'
            ],
        ],
        //Add tab Page Analytics in page Preview
        'meliscms' => [
            'datas' => [
                'page_analytics' => [
                    /**
                     * This is the main display of your page analytics tool.
                     */
                    'melis_cms_page_analytics' => [
                        'conf' => [
                            'id'        => 'melis_cms_page_analytics',
                            'name'      => 'tr_melis_cms_page_analytics',
                            'melisKey'  => 'melis_cms_page_analytics',
                        ],
                        'forward' => [
                            'module'     => 'MelisCmsPageAnalytics',
                            'controller' => 'MelisCmsPageAnalyticsTool',
                            'action'     => 'tool-default-page-analytics-table',
                        ],
                        'interface' => [
                            /**
                             * The display in CMS pages analytics tab
                             */
                            'analytics_for_page' => [
                                'conf' => [
                                    'id'        => 'melis_cms_page_analytics',
                                    'name'      => 'tr_melis_cms_page_analytics',
                                    'melisKey'  => 'melis_cms_page_analytics',
                                ],
                                'forward' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action'     => 'tool-content-container',
                                ],
                            ],
                        ],
                        'datas' => [
                            'settings_form_path' => '',
                        ],
                    ]
                ]
            ],
            'interface' => [
                'meliscms_page' => [
                    'interface'=> [
                        'meliscms_tabs' => [
                            'interface' => [
                                'meliscms_page_analytics_tab' => [
                                    'conf' => [
                                        'id' => 'id_meliscms_page_analytics',
                                        'name' => 'tr_melis_cms_page_analytics_title',
                                        'icon' => 'stats'
                                    ],
                                    'interface' => [
                                        'meliscms_page_analytics_tab_display' => [
                                            'conf'  => [
                                                'id'    => 'id_meliscms_page_analytics',
                                                'name'  => 'tr_meliscms_page_analytics_tab_display',
                                                'melisKey'  => 'meliscms_page_analytics_tab_display',
                                                'rightsDisplay' => 'referencesonly',
                                            ],
                                            'forward' => [
                                                'module' => 'MelisCmsPageAnalytics',
                                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                                'action' => 'tool-container',
                                                'jscallback' => '',
                                                'jsdatas' => []
                                            ],
                                            'interface' => [
                                                'meliscms_page_analytics_tab_header' => [
                                                    'conf' => [
                                                        'id'    => 'id_meliscms_page_analytics_tab_header',
                                                        'name'  => 'tr_meliscms_page_analytics_tab_header',
                                                        'melisKey'  => 'meliscms_page_analytics_tab_header',
                                                        'rightsDisplay' => 'none',
                                                    ],
                                                    'forward' => [
                                                        'module' => 'MelisCmsPageAnalytics',
                                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                                        'action' => 'tool-header-container',
                                                        'jscallback' => '',
                                                        'jsdatas' => []
                                                    ],
                                                ],
                                                // For reloading table in the page
                                                'melis_cms_page_analytics_page_table' => [
                                                    'conf' =>[
                                                        'id' => 'id_melis_cms_page_analytics_page_table',
                                                        'name' => 'tr_melis_cms_page_analytics_page_table',
                                                        'melisKey' => 'melis_cms_page_analytics_page_table'
                                                    ],
                                                    'forward' => [
                                                        'module' => 'MelisCmsPageAnalytics',
                                                        'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                                        'action' => 'tool-content-container'
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        /**
         * this is the configuration of the tool
         */
        'meliscms_page_analytics_tool_config' => [
            'conf' => [
                'id'   => 'id_meliscms_page_analytics_tool_config',
                'name' => 'tr_meliscms_page_analytics_tool_config',
                'melisKey' => 'meliscms_page_analytics_tool_config',
                'rightsDisplay' => 'none'
            ],
            'ressources' => [
                'css' => [
                    '/MelisCmsPageAnalytics/css/site_analytics.css?v=20260630',
                ],
                'js' => [
                    '/MelisCmsPageAnalytics/plugin/ace.js',
                    '/MelisCmsPageAnalytics/js/pagehit.tool.js',
                    '/MelisCmsPageAnalytics/js/page_analytics.tool.js',
                ],
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
            ],
            'interface' => [
                'meliscms_page_analytics_tool_display' => [
                    'conf' => [
                        'id'   => 'id_meliscms_page_analytics_display',
                        'name' => 'tr_meliscms_page_analytics_tool_display_title',
                        'melisKey' => 'meliscms_page_analytics_display',
                        'icon' => 'fa-bar-chart',
                        'rights_checkbox_disable' => true
                    ],
                    'forward' => [
                        'module' => 'MelisCmsPageAnalytics',
                        'controller' => 'MelisCmsPageAnalyticsTool',
                        'action' => 'tool-container',
                        'jscallback' => '',
                        'jsdatas' => []
                    ],
                    'interface' => [
                        'meliscms_page_analytics_header' => [
                            'conf' => [
                                'id'   => 'id_meliscms_page_analytics_header',
                                'name' => 'tr_meliscms_page_analytics_header',
                                'melisKey' => 'meliscms_page_analytics_header',
                            ],
                            'forward' => [
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsTool',
                                'action' => 'tool-header-container',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                        ],
                        'meliscms_page_analytics_content' => [
                            'conf' => [
                                'id'   => 'id_meliscms_page_analytics_content',
                                'name' => 'tr_meliscms_page_analytics_content',
                                'melisKey' => 'meliscms_page_analytics_content',
                            ],
                            'forward' => [
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsTool',
                                'action' => 'tool-content-container',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                            'interface' => [
                                // tab content
                                'meliscms_page_analytics_site_analytics_tab_content' => [
                                    'conf' => [
                                        'id'   => 'id_meliscms_page_analytics_site_analytics_tab_content',
                                        'name' => 'tr_meliscms_page_analytics_tab_content',
                                        'melisKey' => 'meliscms_page_analytics_site_analytics_tab_content',
                                        'icon' => 'glyphicons stats'
                                    ],
                                    'forward' => [
                                        'module' => 'MelisCmsPageAnalytics',
                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                        'action' => 'tool-content-container-analytics-tab-content',
                                        'jscallback' => '',
                                        'jsdatas' => []
                                    ],
                                    'interface' => [
                                        // added to have zone reload only the tab
                                        'melis_cms_page_analytics_tool_table' => [
                                            'conf' =>[
                                                'id' => 'id_melis_cms_page_analytics_tool_table',
                                                'name' => 'tr_melis_cms_page_analytics_tool_table',
                                                'melisKey' => 'melis_cms_page_analytics_tool_table'
                                            ],
                                            'forward' => [
                                                'module' => 'MelisCmsPageAnalytics',
                                                'controller' => 'MelisCmsPageAnalyticsTool',
                                                'action' => 'tool-default-page-analytics-table'
                                            ],
                                        ],
                                    ],
                                ],
                                'meliscms_page_analytics_site_analytics_tab_settings_content' => [
                                    'conf' => [
                                        'id'   => 'id_meliscms_page_analytics_site_analytics_tab_settings_content',
                                        'name' => 'tr_meliscms_page_analytics_settings_tab_content',
                                        'melisKey' => 'meliscms_page_analytics_site_analytics_tab_settings_content',
                                        'icon' => 'glyphicons  settings'

                                    ],
                                    'forward' => [
                                        'module' => 'MelisCmsPageAnalytics',
                                        'controller' => 'MelisCmsPageAnalyticsTool',
                                        'action' => 'tool-content-container-analytics-settings-tab-content',
                                        'jscallback' => '',
                                        'jsdatas' => []
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'meliscms_page_analytics_page_details' => [
                    'conf' => [
                        'id'   => 'id_meliscms_page_analytics_page_details',
                        'name' => 'tr_meliscms_page_analytics_page_detail_title',
                        'melisKey' => 'meliscms_page_analytics_page_details',
                    ],
                    'forward' => [
                        'module' => 'MelisCmsPageAnalytics',
                        'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                        'action'     => 'tool-container-page-analytics-page-details',
                        'jscallback' => '',
                        'jsdatas'    => []
                    ],
                    'interface'   => [
                        'meliscms_page_analytics_page_details_header' => [
                            'conf' => [
                                'id' => 'id_meliscms_page_analytics_page_details_header',
                                'name' => 'tr_meliscms_page_analytics_page_details_title',
                                'melisKey' => 'meliscms_page_analytics_page_details_header',
                            ],
                            'forward' => [
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                'action' => 'tool-header-container',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                        ],
                        'meliscms_page_analytics_page_details_content' => [
                            'conf' => [
                                'id' => 'id_meliscms_page_analytics_page_details_content',
                                'name' => 'tr_meliscms_page_analytics_page_details_content_title',
                                'melisKey' => 'meliscms_page_analytics_page_details_content',
                            ],
                            'forward' => [
                                'module' => 'MelisCmsPageAnalytics',
                                'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                'action' => 'tool-content-container',
                                'jscallback' => '',
                                'jsdatas' => []
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

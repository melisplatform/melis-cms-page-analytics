<?php
namespace MelisCmsPageAnalytics;

 
return array(

'plugins' => array(
    'meliscore' => array(
        'interface' => array(
            'meliscore_leftmenu' => array(
                'interface' => array(
                    'meliscore_toolstree' => array(
                        'interface' => array(
                            'meliscms_tools_section' => array(
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
                        'universal_analytics_tracking_code' => array(
                            'script' => "<script>console.log('You are using Melis Cms Page Analytics');</script>"
                        )
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
                                    'conf' => array(
                                        'id' => 'id_meliscms_page_analytics_tab',
                                        'name' => 'tr_meliscms_page_analytics_tab',
                                        'melisKey'  => 'meliscms_page_analytics_tab',
                                    ),
                                    'interface' => array(
                                        'meliscms_page_analytics_tab_display' => array(
                                            'conf'  => array(
                                                'id'    => 'id_meliscms_page_analytics',
                                                'name'  => 'tr_meliscms_page_analytics_tab_display_title',
                                                'melisKey'  => 'meliscms_page_analytics_tab_display',
                                                'icon'      => 'fa-bar-chart',
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
                                                        'name'  => 'tr_meliscms_page_analytics_header',
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
            'name' => '',
            'id'    => 'meliscms_page_analytics_tool_config',
            'name'  => 'tr_meliscms_page_analytics_tool_config',
            'melisKey'  => 'meliscms_page_analytics_tool_config',
        ),
        'ressources' => array(
            'css' => array(
            ),
            'js' => array(
                'MelisCmsPageAnalytics/js/pagehit.tool.js',
            )
        ),
        'interface' => array(
                'meliscms_page_analytics_tool_display' => array(
                    'conf' => array(
                        'id'   => 'id_meliscms_page_analytics_display',
                        'name' => 'tr_meliscms_page_analytics_tool_display_title',
                        'melisKey' => 'meliscms_page_analytics_display',
                        'icon' => 'fa-bar-chart',
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
                            'interface' => array()
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
                            'interface' => array(
    
                            ),
                        ),
                    ),
                ),
            ),
        ),  
    ),
);
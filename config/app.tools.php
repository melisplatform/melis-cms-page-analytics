<?php  
namespace MelisCmsPageAnalytics;

 return array(
    'plugins' => array(
        'MelisCmsPageAnalytics' => array(
            'tools' => array(
                // page_hit tool table
                'MelisCmsPageAnalytics_tool' => array(
                    'conf' => array(),
                    'table' => array(
                        // the table that will render the data
                        'target' => '#tableMelisCmsPageAnalytics',
                        // the url that will return the JSON album data
                        'ajaxUrl' => '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getMelisCmsPageAnalyticsData',
                        // additional request parameters, this should be a javascript function
                        'dataFunction' => 'melisCmsPageAnalyticsDataFn',
                        // the callback event that will be called after table rendering
                        'ajaxCallback' => 'melisCmsPageAnalyticsAppendLoadedFlag()',
                        'filters' => array(
                            'left' => array(
                                'limit' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-limit',
                                ),
                            ),
                            'center' => array(
                                'melis_cms_page_analytics_tool_search' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-search-tool',
                                ),
                            ),
                            'right' => array(
                                'melis_cms_page_analytics_tool_refresh' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-refresh-tool',
                                ),
                            ),

                        ),
                        'columns' => array(
                            // the key should be the actual column name of the table
                            'ph_id' => array(
                                // text that will be displayed on the table
                                'text' => 'tr_meliscms_page_analytics_column_id',
                                // the width of the column
                                'css'  => array('width' => '10%', 'padding-right' => 0),
                                // if true, then the column is sortable to ASC or DESC
                                'sortable' => true
                            ),
                            'ph_page_id' => array(
                                'text' => 'tr_meliscms_page_analytics_column_page_id',
                                'css'  => array('width' => '10%', 'padding-right' => 0),
                                'sortable' => true
                            ),
                            'page_name' => array(
                                'text' => 'tr_meliscms_page_analytics_column_page_name',
                                'css'  => array('width' => '30%', 'padding-right' => 0),
                                'sortable' => true
                            ),
                            'count' => array(
                                'text' => 'tr_visotrs_count',
                                'css'  => array('width' => '20%', 'padding-right' => 0),
                                'sortable' => true
                            ), 
                            'last_date_visited' => array(
                                'text' => 'tr_meliscms_page_analytics_column_date_visit',
                                'css'  => array('width' => '20%', 'padding-right' => 0),
                                'sortable' => true
                            ),
                            // NOTE: the total width that has been set should not go more than 90%,
                            // because the 10% is reserved to the action column where the buttons will be displayed
                        ),
                        // Set what columns that will be used when searching
                        'searchables' => array('ph_id', 'ph_page_id','page_name','ph_date_visit'),
                        'actionButtons' => array(
                        ),
                    ),
                   
                    'export' => array(
                        // this will be used when an export button is clicked, then the configuration will be used
                        // as the file name of the csv that will be downloaded
                        'csvFileName' => '',
                    ),// end export tool data
                ),
                'MelisCmsPageAnalytics_page_details' => array(
                    'conf' => array(),
                    'table' => array(
                        // the table that will render the data
                        'target' => '#tableMelisCmsPageAnalyticsPageDetails',
                        // the url that will return the JSON album data
                        'ajaxUrl' => '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsPageDetailsTool/getMelisCmsPageAnalyticsPageDetailsData',
                        // additional request parameters, this should be a javascript function
                        'dataFunction' => 'setPageId',
                        // the callback event that will be called after table rendering
                        'ajaxCallback' => 'paginateDataTables()',
                        'filters' => array(
                            'left' => array(
                                'limit' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-limit',
                                ),
                            ),
                            'center' => array(
                                'melis_cms_page_analytics_page_search' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-search-page',
                                ),
                            ),
                            'right' => array(
                                'melis_cms_page_analytics_page_refresh' => array(
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-refresh-page',
                                ),
                            ),

                        ),
                        'columns' => array(
                            // the key should be the actual column name of the table
                            'ph_id' => array(
                                'text' => 'tr_meliscms_page_analytics_column_id',
                                'css'  => array('width' => '20%', 'padding-right' => 0),
                                'sortable' => true
                            ),
                            'ph_date_visit' => array(
                                'text' => 'tr_meliscms_page_analytics_page_details_date_visit',
                                'css'  => array('width' => '40%', 'padding-right' => 0),
                                'sortable' => true
                            ),
                            // NOTE: the total width that has been set should not go more than 90%,
                            // because the 10% is reserved to the action column where the buttons will be displayed
                        ),
                        // Set what columns that will be used when searching
                        'searchables' => array('ph_id', 'ph_date_visit'),
                        'actionButtons' => array(),
                    ),
                   
                    'export' => array(
                        // this will be used when an export button is clicked, then the configuration will be used
                        // as the file name of the csv that will be downloaded
                        'csvFileName' => '',
                    ),// end export tool data
                ),
            ),
        ),
    ),
);
	
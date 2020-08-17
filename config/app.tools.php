<?php  

return [
    'plugins' => [
        'MelisCmsPageAnalytics' => [
            'tools' => [
                // page_hit tool table
                'MelisCmsPageAnalytics_tool' => [
                    'conf' => [],
                    'table' => [
                        // the table that will render the data
                        'target' => '#tableMelisCmsPageAnalytics',
                        // the url that will return the JSON album data
                        'ajaxUrl' => '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/getMelisCmsPageAnalyticsData',
                        // additional request parameters, this should be a javascript function
                        'dataFunction' => 'melisCmsPageAnalyticsDataFn',
                        // the callback event that will be called after table rendering
                        'ajaxCallback' => 'melisCmsPageAnalyticsAppendLoadedFlag()',
                        'filters' => [
                            'left' => [
                                'limit' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-limit',
                                ],
                            ],
                            'center' => [
                                'melis_cms_page_analytics_tool_search' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-search-tool',
                                ],
                            ],
                            'right' => [
                                'melis_cms_page_analytics_tool_refresh' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsTool',
                                    'action' => 'tool-content-table-refresh-tool',
                                ],
                            ],

                        ],
                        'columns' => [
                            // the key should be the actual column name of the table
                            'ph_id' => [
                                // text that will be displayed on the table
                                'text' => 'tr_meliscms_page_analytics_column_id',
                                // the width of the column
                                'css'  => ['width' => '10%', 'padding-right' => 0],
                                // if true, then the column is sortable to ASC or DESC
                                'sortable' => true
                            ],
                            'ph_page_id' => [
                                'text' => 'tr_meliscms_page_analytics_column_page_id',
                                'css'  => ['width' => '10%', 'padding-right' => 0],
                                'sortable' => true
                            ],
                            'page_name' => [
                                'text' => 'tr_meliscms_page_analytics_column_page_name',
                                'css'  => ['width' => '30%', 'padding-right' => 0],
                                'sortable' => true
                            ],
                            'count' => [
                                'text' => 'tr_visotrs_count',
                                'css'  => ['width' => '20%', 'padding-right' => 0],
                                'sortable' => true
                            ], 
                            'last_date_visited' => [
                                'text' => 'tr_meliscms_page_analytics_column_date_visit',
                                'css'  => ['width' => '20%', 'padding-right' => 0],
                                'sortable' => true
                            ],
                            // NOTE: the total width that has been set should not go more than 90%,
                            // because the 10% is reserved to the action column where the buttons will be displayed
                        ],
                        // Set what columns that will be used when searching
                        'searchables' => ['ph_id', 'ph_page_id','page_name','ph_date_visit'],
                        'actionButtons' => [
                        ],
                    ],
                
                    'export' => [
                        // this will be used when an export button is clicked, then the configuration will be used
                        // as the file name of the csv that will be downloaded
                        'csvFileName' => '',
                    ],// end export tool data
                ],
                'MelisCmsPageAnalytics_page_details' => [
                    'conf' => [],
                    'table' => [
                        // the table that will render the data
                        'target' => '#tableMelisCmsPageAnalyticsPageDetails',
                        // the url that will return the JSON album data
                        'ajaxUrl' => '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsPageDetailsTool/getMelisCmsPageAnalyticsPageDetailsData',
                        // additional request parameters, this should be a javascript function
                        'dataFunction' => 'setPageId',
                        // the callback event that will be called after table rendering
                        'ajaxCallback' => '',
                        'filters' => [
                            'left' => [
                                'limit' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-limit',
                                ],
                            ],
                            'center' => [
                                'melis_cms_page_analytics_page_search' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-search-page',
                                ],
                            ],
                            'right' => [
                                'melis_cms_page_analytics_page_refresh' => [
                                    'module' => 'MelisCmsPageAnalytics',
                                    'controller' => 'MelisCmsPageAnalyticsPageDetailsTool',
                                    'action' => 'tool-content-table-refresh-page',
                                ],
                            ],

                        ],
                        'columns' => [
                            // the key should be the actual column name of the table
                            'ph_id' => [
                                'text' => 'tr_meliscms_page_analytics_column_id',
                                'css'  => ['width' => '20%', 'padding-right' => 0],
                                'sortable' => true
                            ],
                            'ph_date_visit' => [
                                'text' => 'tr_meliscms_page_analytics_page_details_date_visit',
                                'css'  => ['width' => '40%', 'padding-right' => 0],
                                'sortable' => true
                            ],
                            // NOTE: the total width that has been set should not go more than 90%,
                            // because the 10% is reserved to the action column where the buttons will be displayed
                        ],
                        // Set what columns that will be used when searching
                        'searchables' => ['ph_id', 'ph_date_visit'],
                        'actionButtons' => [],
                    ],
                    'export' => [
                        // this will be used when an export button is clicked, then the configuration will be used
                        // as the file name of the csv that will be downloaded
                        'csvFileName' => '',
                    ],// end export tool data
                ],
            ],
        ],
    ],
];
    
<?php

return [
    'plugins' => [
        'diagnostic' => [
            'conf' => [
                // user rights exclusions
                'rightsDisplay' => 'none',
            ],
            'MelisCmsPageAnalytics' => [
                'testFolder' => 'test',
                'moduleTestName' => 'MelisCmsPageAnalyticsTest',
                'db' => [
                    // the keys will used as the function name when generated,
                    'getAnalyticsTable' => [
                        'model' => 'MelisCmsPageAnalytics\Model\MelisCmsPageAnalytics',
                        'model_table' => 'MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsTable',
                        'db_table_name' => 'melis_cms_page_analytics',
                    ],
                ],
                'methods' => [
                    'testAddDataOnPage' => [
                        'payloads' => [
                            'page_id' => 1
                        ]
                    ]
                ],
            ],
        ],
    ],
];


<?php

return array(

    'plugins' => array(
        'diagnostic' => array(
            'conf' => array(
                // user rights exclusions
                'rightsDisplay' => 'none',
            ),
            'MelisCmsPageAnalytics' => array(
                'testFolder' => 'test',
                'moduleTestName' => 'MelisCmsPageAnalyticsTest',
                'db' => array(
                    // the keys will used as the function name when generated,
                    'getAnalyticsTable' => array(
                        'model' => 'MelisCmsPageAnalytics\Model\MelisCmsPageAnalytics',
                        'model_table' => 'MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsTable',
                        'db_table_name' => 'melis_cms_page_analytics',
                    ),
                ),
                'methods' => array(
                    'testAddDataOnPage' => array(
                        'payloads' => array(
                            'page_id' => 1
                        )
                    )
                ),
            ),
        ),
    ),


);


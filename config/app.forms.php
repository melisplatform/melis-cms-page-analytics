<?php
return [
    'plugins' => [
        'meliscms' => [
            'forms' => [
                'melis_cms_page_analytics_settings_form' => [
                    'attributes' => [
                        'name'   => 'select_page_analytic_form',
                        'id'     => 'select_page_analytic_form',
                        'method' => 'POST',
                        'action' => null,
                    ],
                    'hydrator' => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => [
                        [
                            'spec' => [
                                'name' => 'pad_site_id',
                                'type' => 'PageAnalyticsSiteSelect',
                                'options' => [
                                    'label' => 'tr_melis_engine_sites',
                                    'tooltip' => 'tr_meliscms_page_analytics_settings_site_select_tooltip',
                                    'empty_option' => 'tr_meliscms_page_analytics_select_site',
                                    'disable_inarray_validator' => true,
                                ],
                                'attributes' => [
                                    'id' => 'site_id',
                                ],
                            ],
                        ],

                        [
                            'spec' => [
                                'name' => 'pad_analytics_key',
                                'type' => 'PageAnalyticsSelect',
                                'options' => [
                                    'label' => 'tr_melis_cms_page_select',
                                    'tooltip' => 'tr_meliscms_page_analytics_settings_select_tooltip',
                                    'empty_option' => 'tr_meliscms_page_analytics_settings_select_blank',
                                    'disable_inarray_validator' => true,
                                ],
                                'attributes' => [
                                    'id' => 'page_analytics_id',
                                ],
                            ],
                        ],
                    ],
                    'input_filter' => [
                        'pad_site_id' => [
                            'name' => 'pad_site_id',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'IsInt',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\I18n\Validator\IsInt::NOT_INT => 'tr_meliscms_page_analytics_site_id_ko',
                                            \Laminas\I18n\Validator\IsInt::INVALID => 'tr_meliscms_page_analytics_site_id_ko',
                                        ],
                                    ]
                                ],
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_page_analytics_site_id_empty',
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ],
                        'pad_analytics_key' => [
                            'name' => 'pad_analytics_key',
                            'required' => true,
                            'validators' => [
                                [
                                    'name' => 'NotEmpty',
                                    'options' => [
                                        'messages' => [
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_page_analytics_settings_select_tooltip_ko',
                                        ],
                                    ],
                                ],
                            ],
                            'filters'  => [
                                ['name' => 'StripTags'],
                                ['name' => 'StringTrim'],
                            ],
                        ]
                    ]
                ]
            ]
        ]
    ]
];
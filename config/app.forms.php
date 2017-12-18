<?php
return array(
    'plugins' => array(
        'meliscms' => array(
            'forms' => array(
                'select_page_analytic_form' => array(
                    'attributes' => array(
                        'name'   => 'select_page_analytic_form',
                        'id'     => 'select_page_analytic_form',
                        'method' => 'POST',
                        'action' => null,
                    ),
                    'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'elements' => array(
                        array(
                            'spec' => array(
                                'name' => 'pad_site_id',
                                'type' => 'PageAnalyticsSiteSelect',
                                'options' => array(
                                    'label' => 'tr_melis_engine_sites',
                                    'tooltip' => 'tr_melis_cms_google_analytics_settings_site_select_tooltip',
                                    'empty_option' => 'tr_meliscms_page_analytics_select_site',
                                    'disable_inarray_validator' => true,
                                ),
                                'attributes' => array(
                                    'id' => 'site_id',
                                ),
                            ),
                        ),

                        array(
                            'spec' => array(
                                'name' => 'pad_analytics_key',
                                'type' => 'PageAnalyticsSelect',
                                'options' => array(
                                    'label' => 'tr_melis_cms_page_select',
                                    'tooltip' => 'tr_meliscms_page_analytics_settings_select_tooltip',
                                    'empty_option' => 'tr_meliscms_page_analytics_settings_select_blank',
                                    'disable_inarray_validator' => true,
                                ),
                                'attributes' => array(
                                    'id' => 'page_analytics_id',
                                ),
                            ),
                        ),
                    ),
                    'input_filter' => array(
                        'pad_site_id' => array(
                            'name' => 'pad_site_id',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'IsInt',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\I18n\Validator\IsInt::NOT_INT => 'tr_meliscms_page_analytics_site_id_ko',
                                            \Zend\I18n\Validator\IsInt::INVALID => 'tr_meliscms_page_analytics_site_id_ko',
                                        ),
                                    )
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_page_analytics_site_id_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                        'pad_analytics_key' => array(
                            'name' => 'pad_analytics_key',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Zend\Validator\NotEmpty::IS_EMPTY => 'tr_meliscms_page_analytics_settings_select_tooltip_ko',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        )
                    )
                )
            )
        )
    )
);
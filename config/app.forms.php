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
                                'name' => 'page_analytics_id',
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
                        'page_analytics_id' => array(
                            'name' => 'page_analytics_id',
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
<?php
return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/melis[/]',
                ],
                'child_routes' => [
                    'application-MelisCmsPageAnalytics' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'MelisCmsPageAnalytics',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                                'controller'    => 'MelisCmsPageAnalyticsTool',
                                'action'        => 'toolContainer',
                            ],
                        ],
                        // this route will be accessible in the browser by browsing
                        // www.domain.com/melis/MelisCmsPageAnalytics/controller/action
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            /*
          * This route will handle the
          * alone setup of a module
          */
            'setup-melis-cms-page-analytics' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/MelisCmsPageAnalytics',
                    'defaults' => [
                        '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                        'controller'    => 'MelisSetup',
                        'action'        => 'setup-form',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [],
                        ],
                    ],
                    'setup' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/setup',
                            'defaults' => [
                                'controller' => 'MelisCmsPageAnalytics\Controller\MelisSetup',
                                'action' => 'setup-form',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            // Tables
            'MelisCmsPageAnalyticsTable'                => \MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsTable::class,
            'MelisCmsPageAnalyticsDataTable'            => \MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsDataTable::class,
            'MelisCmsPageAnalyticsDataSettingsTable'    => \MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsDataSettingsTable::class,
            // Services
            'MelisCmsPageAnalyticsService'              => \MelisCmsPageAnalytics\Service\MelisCmsPageAnalyticsService::class,
            'MelisCmsDefaultPageAnalyticsService'       => \MelisCmsPageAnalytics\Service\MelisCmsDefaultPageAnalyticsService::class,
        ]
    ],

    'controllers' => [
        'invokables' => [
            'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsTool' => 'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsToolController',
            'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsPageDetailsTool' => 'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsPageDetailsToolController',
            'MelisCmsPageAnalytics\Controller\MelisSetup' => 'MelisCmsPageAnalytics\Controller\MelisSetupController',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'PageAnalyticsSelect' => 'MelisCmsPageAnalytics\Form\Factory\PageAnalyticsSelectFactory',
            'PageAnalyticsSiteSelect' => 'MelisCmsPageAnalytics\Form\Factory\PageAnalyticsSiteSelectFactory',
        ],
    ],
    'view_manager' => [
        'doctype'                  => 'HTML5',
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
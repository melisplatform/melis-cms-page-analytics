<?php
return array(
    'router' => array(
        'routes' => array(
            'melis-backoffice' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/melis[/]',
                ),
                'child_routes' => array(
                    'application-MelisCmsPageAnalytics' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'MelisCmsPageAnalytics',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                                'controller'    => 'MelisCmsPageAnalyticsTool',
                                'action'        => 'toolContainer',
                            ),
                        ),
                        // this route will be accessible in the browser by browsing
                        // www.domain.com/melis/MelisCmsPageAnalytics/controller/action
                        'may_terminate' => true,
                        'child_routes' => array(
                            'default' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => array(
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
        'locale' => 'en_EN',
    ),

    'service_manager' => array(
        'invokables' => array(

        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'MelisCmsPageAnalyticsTable' => 'MelisCmsPageAnalytics\Model\Tables\Factory\MelisCmsPageAnalyticsTableFactory',
            'MelisCmsPageAnalyticsDataTable' => 'MelisCmsPageAnalytics\Model\Tables\Factory\MelisCmsPageAnalyticsDataTableFactory',
            'MelisCmsPageAnalyticsSettingsTable' => 'MelisCmsPageAnalytics\Model\Tables\Factory\MelisCmsPageAnalyticsSettingsTableFactory',

            'MelisCmsPageAnalyticsService' => 'MelisCmsPageAnalytics\Service\Factory\MelisCmsPageAnalyticsServiceFactory',
            'MelisCmsDefaultPageAnalyticsService' => 'MelisCmsPageAnalytics\Service\Factory\MelisCmsDefaultPageAnalyticsServiceFactory',

            'MelisCmsPageAnalytics\Listener\MelisCmsPageAnalyticsListener' => 'MelisCmsPageAnalytics\Listener\Factory\MelisCmsPageAnalyticsListenerFactory'
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsTool' => 'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsToolController',
            'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsPageDetailsTool' => 'MelisCmsPageAnalytics\Controller\MelisCmsPageAnalyticsPageDetailsToolController',
        ),
    ),
    'form_elements' => array(
        'factories' => array(
            'PageAnalyticsSelect' => 'MelisCmsPageAnalytics\Form\Factory\PageAnalyticsSelectFactory',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
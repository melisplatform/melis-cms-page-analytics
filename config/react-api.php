<?php

/**
 * Routes + contrôleur React API fournis par MelisCmsPageAnalytics (outil Page Analytics).
 *
 * S'ajoutent aux child_routes de `melis-react-api` (bridge GÉNÉRIQUE de MelisReactApi) via
 * Laminas\Stdlib\ArrayUtils::merge(). Modularité : le contrôleur/routes/invokable de l'outil
 * vivent dans SON module, pas dans MelisReactApi. Mergé via MelisCmsPageAnalytics\Module::getConfig().
 *
 * Ordre : /stats, /sites et /page/:pageId sont des segments distincts (pas de catch-all /:id ici).
 */

return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'child_routes' => [
                    'melis-react-api' => [
                        'child_routes' => [
                            'page-analytics-list' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/page-analytics[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                                        'controller'    => 'MelisReactApiPageAnalytics',
                                        'action'        => 'list',
                                    ],
                                ],
                            ],
                            'page-analytics-stats' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/page-analytics/stats[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                                        'controller'    => 'MelisReactApiPageAnalytics',
                                        'action'        => 'stats',
                                    ],
                                ],
                            ],
                            'page-analytics-sites' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/page-analytics/sites[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCmsPageAnalytics\Controller',
                                        'controller'    => 'MelisReactApiPageAnalytics',
                                        'action'        => 'sites',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MelisCmsPageAnalytics\Controller\MelisReactApiPageAnalytics'
                => \MelisCmsPageAnalytics\Controller\MelisReactApiPageAnalyticsController::class,
        ],
    ],
];

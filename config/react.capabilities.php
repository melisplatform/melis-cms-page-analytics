<?php

/**
 * Capacités React apportées par MelisCmsPageAnalytics — droits avancés du back-office React.
 *
 * MODULARITÉ : ce module CONTRIBUE son onglet « Analytics » à l'éditeur de page CMS sous la MÊME
 * clé `meliscms_page` que MelisCms/SmallBusiness — ArrayUtils::merge fusionne les `tabs` (append).
 * L'onglet devient gatable dans Users→Droits (nœud « Edition de page »). La `key` = melisKey de
 * l'onglet (= son cap côté gating React). Mergé dans Module::getConfig().
 */

return [
    'melisReactToolCapabilities' => [
        'meliscms_page' => [
            'tabs' => [
                ['key' => 'meliscms_page_analytics_tab', 'label' => 'tr_melis_cms_page_analytics_title'],
            ],
        ],
    ],
];

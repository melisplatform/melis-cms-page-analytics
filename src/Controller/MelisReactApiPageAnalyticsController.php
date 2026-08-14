<?php

namespace MelisCmsPageAnalytics\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * API REST (lecture seule) pour l'outil Page Analytics de MelisCmsPageAnalytics.
 *
 * Couche API (shared) du back-office React ; l'UI est livrée par la BRIQUE du module
 * MelisCmsPageAnalytics. Viewer read-only calqué sur le gabarit full-React (cf. Logs) :
 * table agrégée des visites par page (built-in page-hit tracking) + drill-down des visites
 * d'une page. L'onglet « Réglages » (config analytics externe GA/JS) reste en vue legacy (iframe).
 *
 * Tables Engine réutilisées :
 *   - melis_cms_page_analytics       (ph_id, ph_page_id, ph_session_id, ph_date_visit, ph_site_id) = hits bruts
 *   - melis_cms_page_published       (page_id, page_name) = nom de page (LEFT JOIN)
 *   - melis_cms_site                 (site_id, site_name, site_label) = sélecteur de site
 *
 * Routes :
 *   GET /melis/react-api/page-analytics             → liste agrégée par page (recherche + filtre site + tri + pagination)
 *   GET /melis/react-api/page-analytics/stats       → KPI (hits, pages suivies, sites, dernière visite)
 *   GET /melis/react-api/page-analytics/sites        → options du sélecteur de site
 *   GET /melis/react-api/page-analytics/page/:pageId → drill-down : visites individuelles d'une page
 */
class MelisReactApiPageAnalyticsController extends MelisAbstractActionController
{
    /** melisKey of the RIGHTS-BEARING menu node — the access guard (cf. denyUnlessAccess).
     *  NOT `meliscms_page_analytics_display`: that is the `conf.type` target, which stays the
     *  renderable ZONE key (iframe react-tool-page?key=, PageAnalyticsPage.tsx). Since the rights key
     *  moved onto the left-menu path, the target is no longer granted on its own — guarding on it
     *  would 403 every request. This module declares no tool capabilities (its react.capabilities.php
     *  only contributes a tab to `meliscms_page`), so there is nothing else to keep in sync. */
    private const MELIS_KEY = 'meliscms_page_analytics_tools_section';

    /** Colonnes de tri autorisées (liste) → expression SQL AGRÉGÉE utilisée en HAVING/ORDER BY.
     *  ⚠️ Ce sont des agrégats (COUNT/MAX) ou la clé de GROUP BY : ils ne peuvent PAS aller
     *  en WHERE → le keyset est en HAVING (cf. listAction). NON-NULL via COALESCE pour un
     *  ordre stable du curseur opaque. Anti-injection : whitelist stricte. */
    /** Champs du formulaire de l'OUTIL (les deux sélecteurs), rendus nativement par la brique :
     *  jamais présentés comme des « réglages du module » (cf. settingsFieldSpec). */
    private const TOOL_OWN_FIELDS = ['pad_site_id', 'pad_analytics_key'];

    private const SORT_MAP = [
        'pageId'    => 'a.ph_page_id',
        'pageName'  => "COALESCE(MAX(p.page_name),'')",
        'count'     => 'COUNT(a.ph_page_id)',
        'lastVisit' => "COALESCE(MAX(a.ph_date_visit),'1000-01-01 00:00:00')",
    ];

    // ─── GET /page-analytics ─────────────────────────────────────────────────────

    public function listAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            $limit  = min(9999, max(1, (int) $this->params()->fromQuery('limit', 25)));
            $search = trim((string) ($this->params()->fromQuery('search', '') ?? ''));
            $siteId = (int) $this->params()->fromQuery('site', 0) ?: null;

            $sortKey  = (string) $this->params()->fromQuery('sort', 'count');
            $sortExpr = self::SORT_MAP[$sortKey] ?? self::SORT_MAP['count'];
            $dirAsc   = strtoupper((string) $this->params()->fromQuery('dir', 'desc')) === 'ASC';
            $sortDir  = $dirAsc ? 'ASC' : 'DESC';
            $op       = $dirAsc ? '>' : '<';

            // Curseur opaque : base64(json{v, id}) — même schéma que MelisReactKeysetListTrait.
            $cursor = $this->decodeCursor((string) ($this->params()->fromQuery('after', '') ?? ''));

            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');

            [$whereClause, $whereParams] = $this->buildWhere($search, $siteId);

            // Total = nombre de PAGES distinctes (groupes) après filtre — indépendant du curseur.
            $countRow = iterator_to_array($db->query(
                "SELECT COUNT(*) AS total FROM (
                     SELECT a.ph_page_id
                     FROM melis_cms_page_analytics a
                     LEFT JOIN melis_cms_page_published p ON p.page_id = a.ph_page_id
                     $whereClause
                     GROUP BY a.ph_page_id
                 ) t",
                $whereParams
            ));
            $total = (int) ($countRow[0]['total'] ?? 0);

            // Keyset en HAVING (les colonnes de tri sont des agrégats + la clé de GROUP BY) :
            //   (sortExpr op ? OR (sortExpr = ? AND a.ph_page_id op ?))  params [v, v, id]
            $havingClause = '';
            $dataParams   = $whereParams;
            if ($cursor !== null) {
                $havingClause = "HAVING ($sortExpr $op ? OR ($sortExpr = ? AND a.ph_page_id $op ?))";
                $dataParams   = array_merge($dataParams, [$cursor['v'], $cursor['v'], $cursor['id']]);
            }
            $dataParams[] = $limit;

            $rows = $db->query(
                "SELECT a.ph_page_id AS page_id,
                        MAX(p.page_name) AS page_name,
                        COUNT(a.ph_page_id) AS visit_count,
                        MAX(a.ph_date_visit) AS last_visit,
                        $sortExpr AS __sortval
                 FROM melis_cms_page_analytics a
                 LEFT JOIN melis_cms_page_published p ON p.page_id = a.ph_page_id
                 $whereClause
                 GROUP BY a.ph_page_id
                 $havingClause
                 ORDER BY $sortExpr $sortDir, a.ph_page_id $sortDir
                 LIMIT ?",
                $dataParams
            );

            $rows = iterator_to_array($rows);

            $items = [];
            foreach ($rows as $row) {
                $items[] = $this->formatRow((array) $row);
            }

            // Curseur suivant : ssi le lot est plein (sinon fin de liste).
            $nextCursor = null;
            if (count($rows) === $limit && $limit > 0) {
                $last = (array) $rows[count($rows) - 1];
                $nextCursor = base64_encode(json_encode([
                    'v'  => $last['__sortval'],
                    'id' => (int) $last['page_id'],
                ]));
            }

            return $this->jsonResponse([
                'success' => true,
                'data'    => ['items' => $items, 'total' => $total, 'nextCursor' => $nextCursor],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /** Décode le curseur opaque `after` (base64(json{v,id})) ; null si absent/invalide. */
    private function decodeCursor(string $after): ?array
    {
        if ($after === '') { return null; }
        $raw = base64_decode($after, true);
        if ($raw === false) { return null; }
        $data = json_decode($raw, true);
        if (!is_array($data) || !array_key_exists('v', $data) || !array_key_exists('id', $data)) {
            return null;
        }
        return ['v' => $data['v'], 'id' => (int) $data['id']];
    }

    // ─── GET /page-analytics/stats ────────────────────────────────────────────────

    public function statsAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            $search = trim((string) ($this->params()->fromQuery('search', '') ?? ''));
            $siteId = (int) $this->params()->fromQuery('site', 0) ?: null;

            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            [$whereClause, $params] = $this->buildWhere($search, $siteId);

            $row = (array) (iterator_to_array($db->query(
                "SELECT COUNT(*) AS hits,
                        COUNT(DISTINCT a.ph_page_id) AS pages,
                        COUNT(DISTINCT a.ph_site_id) AS sites,
                        MAX(a.ph_date_visit) AS last_visit
                 FROM melis_cms_page_analytics a
                 LEFT JOIN melis_cms_page_published p ON p.page_id = a.ph_page_id
                 $whereClause",
                $params
            ))[0] ?? []);

            return $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'hits'      => (int) ($row['hits'] ?? 0),
                    'pages'     => (int) ($row['pages'] ?? 0),
                    'sites'     => (int) ($row['sites'] ?? 0),
                    'lastVisit' => $row['last_visit'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /page-analytics/sites ────────────────────────────────────────────────

    public function sitesAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            $db   = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $rows = iterator_to_array($db->query(
                'SELECT site_id, site_name, site_label FROM melis_cms_site ORDER BY site_label ASC, site_name ASC',
                []
            ));
            $sites = array_map(fn ($r) => [
                'id'   => (int) $r['site_id'],
                'name' => trim((string) $r['site_label']) !== '' ? (string) $r['site_label'] : (string) $r['site_name'],
            ], $rows);

            return $this->jsonResponse(['success' => true, 'data' => ['sites' => $sites]]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /page-analytics/settings ─────────────────────────────────────────────

    /**
     * État de l'onglet « Paramètres » pour un site (LECTURE seule).
     *
     * L'ÉCRITURE reste l'action legacy `/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save`
     * (POST FormData) : elle porte toute la logique métier (validation Laminas, upload de la clé
     * privée GA, sérialisation de pads_settings, garde admin sur pads_js_analytics, flash messenger)
     * et renvoie déjà du JSON propre `{success, textTitle, textMessage, errors}`. La dupliquer ici
     * ferait diverger les deux vues — la brique React poste donc sur la MÊME action.
     *
     * Paramètres : `site` (obligatoire), `key` (optionnel — module dont on veut le schéma/valeurs ;
     * défaut = le module actuellement affecté au site).
     */
    public function settingsAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            $siteId = (int) $this->params()->fromQuery('site', 0);
            $config = $this->getServiceManager()->get('MelisCoreConfig');

            // Modules analytics disponibles — même source que PageAnalyticsSelectFactory :
            // l'option pseudo « aucun module » + tout ce que les modules ACTIFS déclarent sous
            // meliscms/datas/page_analytics. 100% data-driven : aucun module codé en dur.
            $translator = $this->getServiceManager()->get('translator');
            $modules    = [[
                'key'      => 'melis_cms_no_analytics',
                'label'    => $translator->translate('tr_meliscms_page_analytics_settings_no_analytics'),
                'settings' => false,
            ]];
            foreach ((array) $config->getItem('meliscms/datas/page_analytics') as $key => $cfg) {
                $modules[] = [
                    'key'      => (string) $key,
                    'label'    => $translator->translate($cfg['conf']['name'] ?? $key),
                    // Un module n'a des réglages propres que si son formulaire déclare des champs
                    // AUTRES que les deux sélecteurs de l'outil (cf. settingsFieldSpec).
                    'settings' => $this->settingsFieldSpec($config, (string) $key) !== [],
                    // Affichage React MODULAIRE de l'onglet « Analytics » : un module tiers déclare
                    // `react_display_key` (un melisKey résoluble par react-tool-page) pour son display
                    // site-level. Absent (ex. module natif melis_cms_page_analytics) → l'outil rend sa
                    // table native. 100% data-driven : aucun module codé en dur ici.
                    'displayKey' => isset($cfg['react_display_key']) ? (string) $cfg['react_display_key'] : null,
                ];
            }

            // Module actuellement affecté au site.
            $dataTable     = $this->getServiceManager()->get('MelisCmsPageAnalyticsDataTable');
            $currentKey    = '';
            $jsAnalytics   = '';
            if ($siteId) {
                $current = $dataTable->getEntryByField('pad_site_id', $siteId)->current();
                if (!empty($current)) {
                    $currentKey = (string) $current->pad_analytics_key;
                }
            }

            // Module dont on renvoie le schéma + les valeurs (défaut : celui du site).
            $selectedKey = trim((string) ($this->params()->fromQuery('key', '') ?? '')) ?: $currentKey;

            $fields = [];
            $values = [];
            if ($siteId && $selectedKey !== '' && $selectedKey !== 'melis_cms_no_analytics') {
                $fields = $this->settingsFieldSpec($config, $selectedKey);

                $row = $dataTable->getAnalytics($siteId, $selectedKey)->current();
                if (!empty($row)) {
                    $stored = @unserialize((string) $row->pads_settings);
                    if (is_array($stored)) {
                        foreach ($stored as $name => $value) {
                            // Une clé privée est un CHEMIN serveur : on n'expose que le nom de fichier.
                            $values[$name] = is_string($value) && $this->looksLikePath($name)
                                ? basename($value)
                                : $value;
                        }
                    }
                    $jsAnalytics = (string) ($row->pads_js_analytics ?? '');
                }
            }

            // pads_js_analytics = JS brut injecté dans le <head> de TOUTES les pages front
            // (MelisCmsPageAnalyticsListener) → réservé aux admins plateforme, comme saveAction().
            $identity  = $this->getServiceManager()->get('MelisCoreAuth')->getIdentity();
            $jsEditable = !empty($identity) && !empty($identity->usr_admin);

            return $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'siteId'      => $siteId,
                    'modules'     => $modules,
                    'analyticsKey' => $currentKey,
                    'selectedKey' => $selectedKey,
                    'fields'      => $fields,
                    'values'      => (object) $values, // objet JSON même vide ({} et non [])
                    'jsAnalytics' => $jsAnalytics,
                    'jsEditable'  => $jsEditable,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Traduit le formulaire Laminas d'un module analytics en schéma JSON rendable par la brique.
     * Source : `meliscms/forms/<key>_settings_form` (le même que getSettingsFormAction), donc un
     * module tiers (ex. MelisCmsGoogleAnalytics) est pris en charge sans changement de code ici.
     *
     * ⚠️ Collision de nommage : le module intégré s'appelle `melis_cms_page_analytics` et le
     * formulaire de l'OUTIL lui-même est `melis_cms_page_analytics_settings_form` — la convention
     * `<key>_settings_form` y résout donc le formulaire de l'outil (les deux sélecteurs Site +
     * Module). Le legacy ne s'en aperçoit pas car son JS n'appelle getSettingsForm que pour Google
     * Analytics. On filtre donc les champs propres à l'outil : ce qui reste est bien l'éventuel
     * formulaire de réglages du module (vide pour le module intégré).
     */
    private function settingsFieldSpec($config, string $analyticsKey): array
    {
        $formConfig = $config->getItem('meliscms/forms/' . $analyticsKey . '_settings_form');
        if (empty($formConfig['elements'])) {
            return [];
        }

        $translator = $this->getServiceManager()->get('translator');
        $required   = [];
        foreach ((array) ($formConfig['input_filter'] ?? []) as $name => $filter) {
            if (!empty($filter['required'])) {
                $required[(string) ($filter['name'] ?? $name)] = true;
            }
        }

        $fields = [];
        foreach ($formConfig['elements'] as $element) {
            $spec = $element['spec'] ?? null;
            if (empty($spec['name'])) { continue; }

            $name = (string) $spec['name'];
            if (in_array($name, self::TOOL_OWN_FIELDS, true)) { continue; }

            $type = strtolower((string) ($spec['type'] ?? 'text'));

            $kind = 'text';
            if (str_contains($type, 'file'))           { $kind = 'file'; }
            elseif (str_contains($type, 'textarea'))   { $kind = 'textarea'; }
            elseif (str_contains($type, 'select'))     { $kind = 'select'; }
            elseif (str_contains($type, 'password'))   { $kind = 'password'; }

            $options = [];
            foreach ((array) ($spec['options']['value_options'] ?? []) as $value => $label) {
                $options[] = ['value' => (string) $value, 'label' => $translator->translate((string) $label)];
            }

            $fields[] = [
                'name'     => $name,
                'label'    => $translator->translate((string) ($spec['options']['label'] ?? $name)),
                'tooltip'  => isset($spec['options']['tooltip'])
                    ? $translator->translate((string) $spec['options']['tooltip'])
                    : '',
                'type'     => $kind,
                'required' => !empty($required[$name]),
                'options'  => $options,
            ];
        }

        return $fields;
    }

    /** Champ dont la valeur stockée est un chemin serveur (clé privée GA) → n'exposer que le basename. */
    private function looksLikePath(string $fieldName): bool
    {
        return str_contains($fieldName, 'private_key');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    /** Construit la clause WHERE commune (recherche page_name/id + filtre site). */
    private function buildWhere(string $search, ?int $siteId): array
    {
        $where  = [];
        $params = [];
        if ($search !== '') {
            $like    = '%' . $search . '%';
            $where[] = '(a.ph_page_id LIKE ? OR p.page_name LIKE ?)';
            $params  = array_merge($params, [$like, $like]);
        }
        if ($siteId) {
            $where[]  = 'a.ph_site_id = ?';
            $params[] = $siteId;
        }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$whereClause, $params];
    }

    private function formatRow(array $r): array
    {
        $pageName = trim((string) ($r['page_name'] ?? ''));
        return [
            'pageId'    => (int)    $r['page_id'],
            'pageName'  => $pageName,           // vide = page supprimée (marqué côté React)
            'count'     => (int)    $r['visit_count'],
            'lastVisit' => $r['last_visit'] ?? null,
        ];
    }

    private function isAuthenticated(): bool
    {
        return $this->getServiceManager()->get('MelisCoreAuth')->hasIdentity();
    }

    /** Garde de droits : session + accès à l'outil (401/403/null). */
    private function denyUnlessAccess(): ?HttpResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            if (!$this->getServiceManager()->get('MelisCoreRights')->canAccess(self::MELIS_KEY)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
            }
        } catch (\Throwable) {}
        return null;
    }

    private function jsonResponse(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaders([
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }

    private function errorResponse(\Throwable $e, int $status = 500): HttpResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()) . ':' . $e->getLine(),
        ], $status);
    }
}

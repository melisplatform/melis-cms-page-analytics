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

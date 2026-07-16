<?php

namespace MelisCmsPageAnalytics\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * API REST de l'onglet ANALYTICS de l'éditeur de page — MODULAIRE (melis-cms-page-analytics).
 * Résume les visites d'une page (melis_cms_page_analytics). Distinct du contrôleur de l'OUTIL
 * Analytics (liste globale). Route mergée via le Module getConfig.
 *   GET /melis/react-api/cms-page/analytics?idPage=X&page=N&perPage=100
 *     → { visits, sessions, lastVisit, recent:[{date}], page, perPage, recentTotal }
 *
 * PAGINATION SERVEUR (obligatoire) : la table des visites peut être ÉNORME → on ne charge JAMAIS
 * tout. La liste `recent` est bornée par LIMIT/OFFSET (perPage, défaut 100, plafonné). Le total
 * (`recentTotal` = COUNT indexé sur ph_page_id) sert au calcul des pages côté React.
 */
class MelisReactApiPageAnalyticsTabController extends MelisAbstractActionController
{
    public function getAction(): HttpResponse
    {
        if (!$this->getServiceManager()->get('MelisCoreAuth')->hasIdentity()) {
            return $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            $idPage  = (int) $this->params()->fromQuery('idPage', 0);
            $perPage = (int) $this->params()->fromQuery('perPage', 100);
            $perPage = max(1, min(500, $perPage)); // borne dure : jamais de fetch illimité
            $page    = max(1, (int) $this->params()->fromQuery('page', 1));
            $offset  = ($page - 1) * $perPage;

            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $agg = iterator_to_array($db->query(
                'SELECT COUNT(*) AS visits, MAX(ph_date_visit) AS lastVisit, COUNT(DISTINCT ph_session_id) AS sessions
                 FROM melis_cms_page_analytics WHERE ph_page_id = ?', [$idPage]));
            // $perPage/$offset sont des ENTIERS validés → interpolation sûre (LIMIT/OFFSET ne se bindent
            // pas de façon portable en param préparé). La page renvoyée ne contient QUE perPage lignes.
            $recent = iterator_to_array($db->query(
                "SELECT ph_date_visit AS date FROM melis_cms_page_analytics WHERE ph_page_id = ?
                 ORDER BY ph_date_visit DESC LIMIT $perPage OFFSET $offset", [$idPage]));
            $a = $agg[0] ?? [];
            $total = (int) ($a['visits'] ?? 0);
            return $this->json(['success' => true, 'data' => [
                'idPage'      => $idPage,
                'visits'      => $total,
                'sessions'    => (int) ($a['sessions'] ?? 0),
                'lastVisit'   => $a['lastVisit'] ?? null,
                'recent'      => array_values($recent),
                'page'        => $page,
                'perPage'     => $perPage,
                'recentTotal' => $total, // 1 ligne = 1 visite → total des visites = total paginable
            ]]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function json(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $r */
        $r = $this->getResponse();
        $r->setStatusCode($status);
        $r->getHeaders()->addHeaders(['Content-Type' => 'application/json; charset=utf-8', 'X-Content-Type-Options' => 'nosniff']);
        $r->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $r;
    }
}

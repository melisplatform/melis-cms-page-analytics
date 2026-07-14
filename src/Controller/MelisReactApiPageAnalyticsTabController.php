<?php

namespace MelisCmsPageAnalytics\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * API REST de l'onglet ANALYTICS de l'éditeur de page — MODULAIRE (melis-cms-page-analytics).
 * Résume les visites d'une page (melis_cms_page_analytics). Distinct du contrôleur de l'OUTIL
 * Analytics (liste globale). Route mergée via le Module getConfig.
 *   GET /melis/react-api/cms-page/analytics?idPage=X → { visits, lastVisit, recent:[{date}] }
 */
class MelisReactApiPageAnalyticsTabController extends MelisAbstractActionController
{
    public function getAction(): HttpResponse
    {
        if (!$this->getServiceManager()->get('MelisCoreAuth')->hasIdentity()) {
            return $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            $idPage = (int) $this->params()->fromQuery('idPage', 0);
            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $agg = iterator_to_array($db->query(
                'SELECT COUNT(*) AS visits, MAX(ph_date_visit) AS lastVisit, COUNT(DISTINCT ph_session_id) AS sessions
                 FROM melis_cms_page_analytics WHERE ph_page_id = ?', [$idPage]));
            $recent = iterator_to_array($db->query(
                'SELECT ph_date_visit AS date FROM melis_cms_page_analytics WHERE ph_page_id = ? ORDER BY ph_date_visit DESC LIMIT 20', [$idPage]));
            $a = $agg[0] ?? [];
            return $this->json(['success' => true, 'data' => [
                'idPage'    => $idPage,
                'visits'    => (int) ($a['visits'] ?? 0),
                'sessions'  => (int) ($a['sessions'] ?? 0),
                'lastVisit' => $a['lastVisit'] ?? null,
                'recent'    => array_values($recent),
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

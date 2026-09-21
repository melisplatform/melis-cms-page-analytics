<?php

namespace MelisCmsPageAnalytics\Controller;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * API REST de l'onglet ANALYTICS de l'éditeur de page — MODULAIRE (melis-cms-page-analytics).
 * Résume les visites d'une page (melis_cms_page_analytics). Distinct du contrôleur de l'OUTIL
 * Analytics (liste globale). Route mergée via le Module getConfig.
 *   GET /melis/react-api/cms-page/analytics?idPage=X&page=N&perPage=100
 *     → { visits, sessions, lastVisit, recent:[{date}], page, perPage, recentTotal, analyticsKey, analyticsMessageKey }
 *
 * `analyticsKey` (Mantis #0011034) = clé du module d'analytics AFFECTÉ au site de la page (Site
 * Analytics → Settings, table melis_cms_page_analytics_data), MAIS SEULEMENT si cette clé correspond
 * à une entrée réellement déclarée sous meliscms/datas/page_analytics — même contrôle que le legacy
 * (MelisCmsPageAnalyticsPageDetailsToolController::toolContainerAction, $hasAnalyticsConfig). Sinon
 * (rien configuré, sentinel « aucun module », ou module désactivé/désinstallé) : null, accompagné de
 * `analyticsMessageKey` (une des deux clés de traduction legacy) pour que le tab affiche le MÊME
 * message que le legacy plutôt que les visites internes de Melis. « Si rien n'est configuré, alors
 * rien ne s'affiche » (Mantis #0011034) — voir aussi PageTabs.tsx côté melis-cms.
 *
 * L'onglet « Page Analytics » de l'éditeur React (melis-cms, le SEUL onglet d'analytics) utilise un
 * `analyticsKey` valide pour monter l'affichage natif enregistré par ce module (ex. GA :
 * window.__melisAnalyticsPageDisplays[clé]) à la place de la vue Melis ci-dessous.
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
            $analyticsKey = null;
            $analyticsMessageKey = null;
            try {
                $config = $this->getServiceManager()->get('MelisCoreConfig');
                $site = $this->getServiceManager()->get('MelisEngineTree')->getSiteByPageId($idPage);
                $siteId = empty($site->site_id) ? 0 : (int) $site->site_id;
                if ($siteId) {
                    $row = $this->getServiceManager()->get('MelisCmsPageAnalyticsDataTable')->getEntryByField('pad_site_id', $siteId)->current();
                    $rawKey = ($row && !empty($row->pad_analytics_key)) ? (string) $row->pad_analytics_key : null;
                    if ($rawKey === null) {
                        // Jamais configuré pour ce site — MÊME message que le legacy (parité stricte,
                        // MelisCmsPageAnalyticsPageDetailsToolController::toolContainerAction, branche
                        // "else" de "if ($curData)").
                        $analyticsMessageKey = 'tr_meliscms_page_analytics_no_module_set';
                    } else {
                        // Une clé est enregistrée : NE VAUT comme module réel QUE si elle correspond à
                        // une entrée effectivement déclarée sous meliscms/datas/page_analytics — EXACTEMENT
                        // le contrôle du legacy ($hasAnalyticsConfig). Ça exclut à la fois le sentinel
                        // 'melis_cms_no_analytics' (jamais déclaré là, ajouté seulement par le select) et
                        // toute clé d'un module désormais désactivé/désinstallé (même message que legacy :
                        // « module désactivé »). Mantis #0011034 : sans ce contrôle, TOUTE valeur non vide
                        // (y compris le sentinel « aucun module ») repassait telle quelle à React, qui la
                        // traitait comme « un module est assigné » et affichait quand même le tableau de
                        // visites natif — la même violation du « si rien, alors rien » que le legacy évite
                        // depuis toujours via ce même contrôle.
                        $hasAnalyticsConfig = $config->getItem('meliscms/datas/page_analytics/' . $rawKey);
                        if ($hasAnalyticsConfig) {
                            $analyticsKey = $rawKey;
                        } else {
                            $analyticsMessageKey = 'tr_meliscms_page_analytics_inactive_module';
                        }
                    }
                } else {
                    $analyticsMessageKey = 'tr_meliscms_page_analytics_no_module_set';
                }
            } catch (Throwable) { $analyticsKey = null; $analyticsMessageKey = null; } // jamais bloquant : la vue Melis reste disponible
            return $this->json(['success' => true, 'data' => [
                'idPage'      => $idPage,
                'visits'      => $total,
                'sessions'    => (int) ($a['sessions'] ?? 0),
                'lastVisit'   => $a['lastVisit'] ?? null,
                'recent'      => array_values($recent),
                'page'        => $page,
                'perPage'     => $perPage,
                'recentTotal' => $total, // 1 ligne = 1 visite → total des visites = total paginable
                'analyticsKey' => $analyticsKey,
                'analyticsMessageKey' => $analyticsMessageKey,
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

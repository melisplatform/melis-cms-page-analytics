import { Fragment, useEffect, useRef, useState } from 'react'
import {
  fetchAnalytics, fetchAnalyticsStats, fetchAnalyticsSites, fetchAnalyticsSettings,
  type AnalyticsRow, type AnalyticsStats, type SiteOption,
} from './page-analytics-api'
import { useKeysetList } from './use-keyset-list'
import {
  useT, currentLang, fmtDate, card, inputCss, btnGhost, th, td,
  Kpi, GripIcon, ColManager, makeColStore, visibleCols, type ColDef,
  IconEye, IconFileText, IconGlobe, IconClock,
} from './ui'
import { ExportModal, DownloadIcon } from './ExportModal'
import { ViewToggle, type ViewMode } from './ViewToggle'
import SettingsPanel from './SettingsPanel'
import { useIsNarrow } from './shared/useIsNarrow'
import { ExpandToggle, HiddenColsRow } from './shared/ExpandableRow'

/**
 * Outil Site Analytics (brique MelisCmsPageAnalytics).
 * En-tête PERSISTANT (titre + toggle New/Old) → le toggle reste toujours accessible.
 *  • « New » : vue React native à DEUX onglets, miroir du legacy —
 *      · « Analytics »  : table des visites par page (sélecteur de site, KPI, recherche,
 *                         colonnes, export). Lecture seule, sans drill-down (fidèle au legacy).
 *      · « Paramètres » : affectation d'un module analytics à un site + réglages du module
 *                         + script JS personnalisé (cf. SettingsPanel).
 *  • « Old » : le même outil legacy complet en iframe, pour comparaison.
 * Brique `persistent` : état + iframe préservés en changeant d'onglet outil.
 */

type Tab = 'analytics' | 'settings'

const MELIS_KEY = 'meliscms_page_analytics_display' // zone legacy rendable (vue « Old »)

/** Icône de tri unifiée — mêmes tracés que les icônes lucide ArrowUpDown/ArrowUp/ArrowDown du core. */
function SortIcon({ dir }: { dir: 'asc' | 'desc' | null }) {
  const p = { width: 12, height: 12, viewBox: '0 0 24 24', fill: 'none' as const, stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const, style: { flexShrink: 0, opacity: dir ? 1 : 0.3 } }
  if (dir === 'asc')  return <svg {...p}><path d="m5 12 7-7 7 7" /><path d="M12 19V5" /></svg>
  if (dir === 'desc') return <svg {...p}><path d="M12 5v14" /><path d="m19 12-7 7-7-7" /></svg>
  return <svg {...p}><path d="m21 16-4 4-4-4" /><path d="M17 20V4" /><path d="m3 8 4-4 4 4" /><path d="M7 4v16" /></svg>
}

/** Onglets natifs « Analytics » / « Paramètres » — même découpage que l'outil legacy. */
function TabBar({ tab, onChange, narrow }: { tab: Tab; onChange: (t: Tab) => void; narrow: boolean }) {
  const t = useT()
  const item = (id: Tab, text: string) => (
    <button key={id} type="button" onClick={() => onChange(id)}
      style={{
        appearance: 'none', border: 0, background: 'transparent', cursor: 'pointer',
        padding: '10px 4px', fontSize: 14, fontWeight: tab === id ? 600 : 500,
        color: tab === id ? 'var(--color-foreground)' : 'var(--color-muted-foreground)',
        borderBottom: `2px solid ${tab === id ? 'var(--color-primary)' : 'transparent'}`,
      }}>{text}</button>
  )
  return (
    <div style={{ display: 'flex', gap: narrow ? 16 : 20, flexWrap: narrow ? 'wrap' : 'nowrap', padding: narrow ? '0 16px' : '0 24px', borderBottom: '1px solid var(--color-border)', flexShrink: 0 }}>
      {item('analytics', t('tab_analytics'))}
      {item('settings', t('tab_settings'))}
    </div>
  )
}

export default function PageAnalyticsPage() {
  const t = useT()
  const narrow = useIsNarrow()
  const [mode, setMode] = useState<ViewMode>('react')
  const [tab, setTab] = useState<Tab>('analytics')
  const [frameLoaded, setFrameLoaded] = useState(false)
  const [site, setSite] = useState(0) // 0 = tous les sites

  const subtitle = mode === 'iframe' ? t('old_hint') : (tab === 'settings' ? t('set_subtitle') : t('subtitle'))

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      {/* En-tête PERSISTANT (titre + toggle) — visible dans les deux modes */}
      {/* Étroit : titre + toggle restent sur UNE ligne (le titre rétrécit via minWidth 0), plutôt
          que d'empiler deux barres pleine largeur — le toggle passe en mode `compact` (icônes). */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: narrow ? 8 : 16, flexWrap: narrow ? 'nowrap' : 'wrap', padding: narrow ? '16px 16px 10px' : '20px 24px 12px', flexShrink: 0 }}>
        <div style={narrow ? { minWidth: 0 } : undefined}>
          <h1 style={{ fontSize: narrow ? 17 : 20, fontWeight: 700, margin: 0, ...(narrow ? { overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } : {}) }}>{t('title')}</h1>
          <p style={{ fontSize: narrow ? 12 : 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0', ...(narrow ? { overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } : {}) }}>{subtitle}</p>
        </div>
        <div style={{ flexShrink: 0 }}>
          <ViewToggle mode={mode} compact={narrow} onChange={(m) => { setMode(m); if (m === 'iframe') setFrameLoaded(true) }} labels={{ react: t('view_new'), iframe: t('view_old') }} />
        </div>
      </div>

      {/* Onglets natifs — uniquement en vue React (l'outil legacy a déjà les siens) */}
      {mode === 'react' && <TabBar tab={tab} onChange={setTab} narrow={narrow} />}

      {/* Corps : les vues natives OU l'outil legacy, tous remplissant l'espace restant */}
      <div style={{ flex: 1, minHeight: 0, position: 'relative' }}>
        {frameLoaded && (
          <div style={{ position: 'absolute', inset: 0, display: mode === 'iframe' ? 'block' : 'none' }}>
            <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`}
              style={{ width: '100%', height: '100%', border: 0 }} title={`${t('title')} — Vue Melis`} />
          </div>
        )}
        {/* La liste reste MONTÉE quand on passe aux paramètres (scroll infini + colonnes préservés). */}
        <div style={{ position: 'absolute', inset: 0, overflow: 'auto', display: mode === 'react' && tab === 'analytics' ? 'block' : 'none' }}>
          <AnalyticsList site={site} onSite={setSite} narrow={narrow} />
        </div>
        {mode === 'react' && tab === 'settings' && (
          <div style={{ position: 'absolute', inset: 0, overflow: 'auto' }}>
            <SettingsPanel />
          </div>
        )}
      </div>
    </div>
  )
}

// ── Liste analytics (agrégée par page) ────────────────────────────────────────
const COL_LABEL: Record<string, string> = {
  pageId: 'col_pageId', pageName: 'col_pageName', count: 'col_count', lastVisit: 'col_last',
}
const DEFAULT_COLS: ColDef[] = [
  { id: 'pageId', visible: true }, { id: 'pageName', visible: true },
  { id: 'count', visible: true }, { id: 'lastVisit', visible: true },
]
const colStore = makeColStore('melis-page-analytics-cols-v1', DEFAULT_COLS)

function AnalyticsList({ site, onSite, narrow }: { site: number; onSite: (id: number) => void; narrow: boolean }) {
  const t = useT()
  const lang = currentLang()
  const [stats, setStats] = useState<AnalyticsStats | null>(null)
  const [sites, setSites] = useState<SiteOption[]>([])
  const [search, setSearch] = useState('')
  const [cols, setCols] = useState<ColDef[]>(colStore.load)
  const [showCols, setShowCols] = useState(false)
  const [showExport, setShowExport] = useState(false)
  const [tick, setTick] = useState(0)
  const [expanded, setExpanded] = useState<Set<number>>(() => new Set())
  const colsAnchorRef = useRef<HTMLDivElement>(null)

  // Affichage MODULAIRE : si le site sélectionné a pour module d'analytics un module tiers qui
  // déclare un `displayKey` (ex. Google Analytics → react_display_key), on rend SON affichage
  // (iframe react-tool-page site-level) au lieu de la table native. Le module affecté au site vient
  // de /settings (analyticsKey). « Tous les sites » (site=0) ou module natif → table native agrégée.
  const [moduleDisplayKey, setModuleDisplayKey] = useState<string | null>(null)
  useEffect(() => {
    if (!site) { setModuleDisplayKey(null); return }
    let alive = true
    fetchAnalyticsSettings(site)
      .then((s) => { if (alive) setModuleDisplayKey(s.modules.find((m) => m.key === s.analyticsKey)?.displayKey ?? null) })
      .catch(() => { if (alive) setModuleDisplayKey(null) })
    return () => { alive = false }
  }, [site])
  const moduleMode = !!moduleDisplayKey

  // A Hidden column disappears entirely on both desktop and mobile — same rule everywhere, no "+"
  // peek at Hidden ones. Desktop shows every Visible column inline. Mobile can't fit many columns,
  // so only the FIRST Visible column (by the user's dragged order in ColManager) anchors inline;
  // every OTHER Visible column surfaces behind the per-row "+" instead, in that same order.
  const shownColsList = cols.filter((c) => c.visible)
  const displayCols = narrow ? shownColsList.map((c, i) => ({ ...c, visible: i === 0 })) : shownColsList
  const hasHidden = narrow && shownColsList.length > 1
  const shownCols = visibleCols(displayCols)

  // Liste keyset (scroll infini + tri server-side). Recherche = filtre SERVER-SIDE (buildWhere),
  // capturée par la closure `fetcher` + déclencheur de rechargement via `deps`.
  const { items, total, loading, hasMore, sentinelRef, sortCol, sortDir, toggleSort } =
    useKeysetList<AnalyticsRow>({
      fetcher: (a) => fetchAnalytics({
        search, site, limit: a.limit, sort: a.sort, dir: a.dir, after: a.after ?? undefined,
      }).then((r) => ({ items: r.items, total: r.total, nextCursor: r.nextCursor })),
      deps: [search, site, tick],
      defaultSort: 'count',
      defaultDir: 'desc',
    })

  useEffect(() => { fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null) }, [])
  useEffect(() => { fetchAnalyticsStats({ search, site }).then(setStats).catch(() => null) }, [search, site, tick])

  const cell = (r: AnalyticsRow, id: string): string => {
    switch (id) {
      case 'pageId': return String(r.pageId)
      case 'pageName': return r.pageName || t('deleted')
      case 'count': return String(r.count)
      case 'lastVisit': return fmtDate(r.lastVisit, lang)
      default: return ''
    }
  }

  // Export : parcours du curseur (lots de 100) jusqu'à épuisement (nextCursor null).
  const fetchAll = async (): Promise<AnalyticsRow[]> => {
    const acc: AnalyticsRow[] = []
    let after: string | null = null
    for (;;) {
      const r = await fetchAnalytics({ search, site, sort: sortCol, dir: sortDir, limit: 100, after })
      acc.push(...r.items)
      if (!r.nextCursor) break
      after = r.nextCursor
    }
    return acc
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: narrow ? 16 : 20, padding: narrow ? 16 : 24, boxSizing: 'border-box' }}>
      {/* KPI — 2 par ligne sur viewport étroit (cf. flag `narrow` du composant Kpi). Cachés en
          affichage modulaire (le module tiers rend ses propres indicateurs). */}
      {!moduleMode && (
      <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
        <Kpi label={t('kpi_hits')} value={stats?.hits ?? null} narrow={narrow} icon={<IconEye />} tint="#2563eb" />
        <Kpi label={t('kpi_pages')} value={stats?.pages ?? null} narrow={narrow} icon={<IconFileText />} tint="var(--color-primary)" />
        <Kpi label={t('kpi_sites')} value={stats?.sites ?? null} narrow={narrow} icon={<IconGlobe />} tint="#7c3aed" />
        <Kpi label={t('kpi_last')} value={stats ? fmtDate(stats.lastVisit, lang) : null} narrow={narrow} icon={<IconClock />} tint="#d97706" />
      </div>
      )}

      {/* Barre d'outils : site + recherche + colonnes + export + refresh.
          Étroit : sélecteur et recherche pleine largeur, puis les 3 boutons sur une ligne
          (Colonnes/Exporter se partagent la place, le refresh garde sa largeur d'icône). */}
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' }}>
        <select style={{ ...inputCss, height: 36, ...(narrow ? { width: '100%' } : { width: 'auto', minWidth: 180 }) }} value={site} onChange={(e) => onSite(Number(e.target.value))}>
          <option value={0}>{t('site_all')}</option>
          {sites.map((s) => <option key={s.id} value={s.id}>{s.name} (#{s.id})</option>)}
        </select>
        {/* Recherche + colonnes + export + refresh : propres à la table native → cachés en mode module. */}
        {!moduleMode && (
        <>
        <input style={{ ...inputCss, height: 36, ...(narrow ? { width: '100%' } : { flex: 1, minWidth: 200 }) }} value={search}
          onChange={(e) => setSearch(e.target.value)} placeholder={t('search')} />
        <div style={{ display: 'flex', gap: 8, alignItems: 'center', ...(narrow ? { width: '100%' } : {}) }}>
          <div ref={colsAnchorRef} style={{ position: 'relative', ...(narrow ? { flex: '1 1 0', minWidth: 0 } : {}) }}>
            <button style={{ ...btnGhost, height: 36, ...(narrow ? { width: '100%', justifyContent: 'center' } : {}) }} onClick={() => setShowCols((v) => !v)}><GripIcon />{t('columns')}</button>
            {showCols && <ColManager anchorRef={colsAnchorRef} cols={cols} labelFor={(id) => t(COL_LABEL[id])} onChange={setCols} onSave={colStore.save} defaults={colStore.defaults} onClose={() => setShowCols(false)} />}
          </div>
          <button style={{ ...btnGhost, height: 36, ...(narrow ? { flex: '1 1 0', minWidth: 0, justifyContent: 'center' } : {}) }} onClick={() => setShowExport(true)}><DownloadIcon />{t('export')}</button>
          <button style={{ ...btnGhost, height: 36, flexShrink: 0 }} onClick={() => setTick((x) => x + 1)} title={t('refresh')}>↻</button>
        </div>
        </>
        )}
      </div>

      {/* Affichage du module tiers (ex. Google Analytics) affecté au site : son display site-level
          rendu en iframe (react-tool-page → tool-display-iframe?siteId=X). Modulaire : la clé vient
          de la config du module (react_display_key), zéro hardcode ici. */}
      {moduleMode && moduleDisplayKey && (
        <div style={{ ...card, overflow: 'hidden', minHeight: 560, display: 'flex' }}>
          <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(moduleDisplayKey)}&siteId=${site}`}
            style={{ width: '100%', height: '100%', minHeight: 560, border: 0, display: 'block' }} title={t('title')} />
        </div>
      )}

      {/* Table native (module d'analytics par défaut / aucun module) */}
      {!moduleMode && (
      <div style={{ ...card, overflow: 'hidden' }}>
        {/* Étroit : on retire le minWidth, sinon le repli des colonnes ne sert à rien (scroll H). */}
        <table style={{ width: '100%', borderCollapse: 'collapse', ...(narrow ? {} : { minWidth: 560 }) }}>
          <thead style={{ background: 'var(--color-muted,rgba(0,0,0,.03))' }}>
            <tr>
              {hasHidden && <th style={{ ...th, width: 40, padding: '10px 8px 10px 12px' }} />}
              {shownCols.map(({ id }) => (
                <th key={id} style={{ ...th, cursor: 'pointer', ...(sortCol === id ? { color: 'var(--color-primary)' } : {}) }} onClick={() => toggleSort(id)}>
                  <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4 }}>{t(COL_LABEL[id])}<SortIcon dir={sortCol === id ? sortDir : null} /></span>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && !loading ? (
              <tr><td style={{ ...td, textAlign: 'center', color: 'var(--color-muted-foreground)', padding: '40px 16px' }} colSpan={shownCols.length + (hasHidden ? 1 : 0)}>{t('empty')}</td></tr>
            ) : items.map((r) => (
              <Fragment key={r.pageId}>
                <tr>
                  {/* Bascule « + » en colonne la PLUS À GAUCHE : c'est là qu'elle se lit comme
                      « déplier cette ligne » (noyée à droite, personne ne la trouve). */}
                  {hasHidden && (
                    <td style={{ ...td, width: 40, padding: '10px 8px 10px 12px' }}>
                      <ExpandToggle expanded={expanded.has(r.pageId)} onClick={() => setExpanded((prev) => {
                        const next = new Set(prev)
                        if (!next.delete(r.pageId)) next.add(r.pageId)
                        return next
                      })} />
                    </td>
                  )}
                  {shownCols.map(({ id }) => (
                    <td key={id} style={{ ...td, ...(id === 'pageId' || id === 'count' ? { color: 'var(--color-muted-foreground)', fontVariantNumeric: 'tabular-nums' } : {}) }}>
                      {id === 'pageName' && !r.pageName
                        ? <span style={{ fontStyle: 'italic', color: 'var(--color-muted-foreground)' }}>{t('deleted')}</span>
                        : cell(r, id)}
                    </td>
                  ))}
                </tr>
                {hasHidden && expanded.has(r.pageId) && (
                  <HiddenColsRow cols={displayCols} labelFor={(id) => t(COL_LABEL[id])} narrow={narrow}
                    colSpan={shownCols.length + 1}
                    renderValue={(id) => (id === 'pageName' && !r.pageName
                      ? <span style={{ fontStyle: 'italic', color: 'var(--color-muted-foreground)' }}>{t('deleted')}</span>
                      : cell(r, id))} />
                )}
              </Fragment>
            ))}
          </tbody>
        </table>
        {/* Sentinel scroll infini : visible → charge le lot suivant. */}
        <div ref={sentinelRef} style={{ height: 1 }} />
        <div style={{ padding: '10px 16px', textAlign: 'center', fontSize: 12, color: 'var(--color-muted-foreground)' }}>
          {loading ? t('loading') : (!hasMore && items.length > 0 ? t('count', { n: total }) : '')}
        </div>
      </div>
      )}

      {showExport && (
        <ExportModal<AnalyticsRow>
          cols={cols}
          labelFor={(id) => t(COL_LABEL[id])}
          fetchAll={fetchAll}
          getCell={(r, id) => cell(r, id)}
          filename="page-analytics" sheetName={t('title')} total={total}
          onClose={() => setShowExport(false)} />
      )}
    </div>
  )
}

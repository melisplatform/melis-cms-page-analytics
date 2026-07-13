import { useEffect, useMemo, useState } from 'react'
import {
  fetchAnalytics, fetchAnalyticsStats, fetchAnalyticsSites,
  type AnalyticsRow, type AnalyticsStats, type SiteOption,
} from './page-analytics-api'
import {
  useT, currentLang, fmtDate, card, inputCss, btnGhost, th, td,
  Kpi, GripIcon, ColManager, makeColStore, visibleCols, type ColDef,
} from './ui'
import { ExportModal, DownloadIcon } from './ExportModal'
import { ViewToggle, type ViewMode } from './ViewToggle'

/**
 * Outil Site Analytics (brique MelisCmsPageAnalytics).
 * En-tête PERSISTANT (titre + toggle New/Old) → le toggle reste toujours accessible.
 *  • « New » : table React native des visites par page (sélecteur de site, KPI, recherche,
 *    colonnes, export). Lecture seule, sans drill-down (fidèle au legacy).
 *  • « Old » : outil legacy complet en iframe — onglets « Analytics » + « Paramètres »
 *    (affectation du module analytics aux sites, config GA/JS). Non migré, géré en legacy.
 * Brique `persistent` : état + iframe préservés en changeant d'onglet outil.
 */

const MELIS_KEY = 'meliscms_page_analytics_display' // zone legacy rendable (vue « Old »)

export default function PageAnalyticsPage() {
  const t = useT()
  const [mode, setMode] = useState<ViewMode>('react')
  const [frameLoaded, setFrameLoaded] = useState(false)
  const [site, setSite] = useState(0) // 0 = tous les sites

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      {/* En-tête PERSISTANT (titre + toggle) — visible dans les deux modes */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap', padding: '20px 24px 12px', flexShrink: 0 }}>
        <div>
          <h1 style={{ fontSize: 20, fontWeight: 700, margin: 0 }}>{t('title')}</h1>
          <p style={{ fontSize: 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0' }}>
            {mode === 'iframe' ? t('old_hint') : t('subtitle')}
          </p>
        </div>
        <ViewToggle mode={mode} onChange={(m) => { setMode(m); if (m === 'iframe') setFrameLoaded(true) }} />
      </div>

      {/* Corps : la table native OU l'outil legacy, tous deux remplissant l'espace restant */}
      <div style={{ flex: 1, minHeight: 0, position: 'relative' }}>
        {frameLoaded && (
          <div style={{ position: 'absolute', inset: 0, display: mode === 'iframe' ? 'block' : 'none' }}>
            <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`}
              style={{ width: '100%', height: '100%', border: 0 }} title={`${t('title')} — Vue Melis`} />
          </div>
        )}
        <div style={{ position: 'absolute', inset: 0, overflow: 'auto', display: mode === 'react' ? 'block' : 'none' }}>
          <AnalyticsList site={site} onSite={setSite} />
        </div>
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

function AnalyticsList({ site, onSite }: { site: number; onSite: (id: number) => void }) {
  const t = useT()
  const lang = currentLang()
  const [rows, setRows] = useState<AnalyticsRow[]>([])
  const [stats, setStats] = useState<AnalyticsStats | null>(null)
  const [sites, setSites] = useState<SiteOption[]>([])
  const [loading, setLoading] = useState(false)
  const [search, setSearch] = useState('')
  const [sortCol, setSortCol] = useState<string>('count')
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc')
  const [cols, setCols] = useState<ColDef[]>(colStore.load)
  const [showCols, setShowCols] = useState(false)
  const [showExport, setShowExport] = useState(false)
  const [tick, setTick] = useState(0)

  useEffect(() => { fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null) }, [])
  useEffect(() => { fetchAnalyticsStats({ site }).then(setStats).catch(() => null) }, [site, tick])
  useEffect(() => {
    setLoading(true)
    fetchAnalytics({ site, limit: 9999 }).then((r) => setRows(r.items)).catch(() => null).finally(() => setLoading(false))
  }, [site, tick])

  const cell = (r: AnalyticsRow, id: string): string => {
    switch (id) {
      case 'pageId': return String(r.pageId)
      case 'pageName': return r.pageName || t('deleted')
      case 'count': return String(r.count)
      case 'lastVisit': return fmtDate(r.lastVisit, lang)
      default: return ''
    }
  }
  const sortVal = (r: AnalyticsRow, id: string): number | string => {
    switch (id) {
      case 'pageId': return r.pageId
      case 'count': return r.count
      case 'lastVisit': return r.lastVisit ?? ''
      default: return (r.pageName || '').toLowerCase()
    }
  }

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    let list = rows
    if (q) list = rows.filter((r) => (r.pageName || '').toLowerCase().includes(q) || String(r.pageId).includes(q))
    const dir = sortDir === 'asc' ? 1 : -1
    return [...list].sort((a, b) => {
      const va = sortVal(a, sortCol), vb = sortVal(b, sortCol)
      if (va < vb) return -1 * dir
      if (va > vb) return 1 * dir
      return 0
    })
  }, [rows, search, sortCol, sortDir])

  function toggleSort(id: string) {
    if (sortCol === id) setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'))
    else { setSortCol(id); setSortDir(id === 'pageName' ? 'asc' : 'desc') }
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 20, padding: 24, boxSizing: 'border-box' }}>
      {/* KPI */}
      <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
        <Kpi label={t('kpi_hits')} value={stats?.hits ?? null} />
        <Kpi label={t('kpi_pages')} value={stats?.pages ?? null} />
        <Kpi label={t('kpi_sites')} value={stats?.sites ?? null} />
        <Kpi label={t('kpi_last')} value={stats ? fmtDate(stats.lastVisit, lang) : null} />
      </div>

      {/* Barre d'outils : site + recherche + colonnes + export + refresh */}
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' }}>
        <select style={{ ...inputCss, height: 36, width: 'auto', minWidth: 180 }} value={site} onChange={(e) => onSite(Number(e.target.value))}>
          <option value={0}>{t('site_all')}</option>
          {sites.map((s) => <option key={s.id} value={s.id}>{s.name} (#{s.id})</option>)}
        </select>
        <input style={{ ...inputCss, height: 36, flex: 1, minWidth: 200 }} value={search}
          onChange={(e) => setSearch(e.target.value)} placeholder={t('search')} />
        <div style={{ position: 'relative' }}>
          <button style={{ ...btnGhost, height: 36 }} onClick={() => setShowCols((v) => !v)}><GripIcon />{t('columns')}</button>
          {showCols && <ColManager cols={cols} labelFor={(id) => t(COL_LABEL[id])} onChange={setCols} onSave={colStore.save} defaults={colStore.defaults} onClose={() => setShowCols(false)} />}
        </div>
        <button style={{ ...btnGhost, height: 36 }} onClick={() => setShowExport(true)}><DownloadIcon />{t('export')}</button>
        <button style={{ ...btnGhost, height: 36 }} onClick={() => setTick((x) => x + 1)} title={t('refresh')}>↻</button>
      </div>

      {/* Table */}
      <div style={{ ...card, overflow: 'hidden' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: 560 }}>
          <thead style={{ background: 'var(--color-muted,rgba(0,0,0,.03))' }}>
            <tr>
              {visibleCols(cols).map(({ id }) => (
                <th key={id} style={{ ...th, cursor: 'pointer' }} onClick={() => toggleSort(id)}>
                  {t(COL_LABEL[id])}{sortCol === id ? ` ${sortDir === 'asc' ? '↑' : '↓'}` : ''}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filtered.length === 0 && !loading ? (
              <tr><td style={{ ...td, textAlign: 'center', color: 'var(--color-muted-foreground)', padding: '40px 16px' }} colSpan={visibleCols(cols).length}>{t('empty')}</td></tr>
            ) : filtered.map((r) => (
              <tr key={r.pageId}>
                {visibleCols(cols).map(({ id }) => (
                  <td key={id} style={{ ...td, ...(id === 'pageId' || id === 'count' ? { color: 'var(--color-muted-foreground)', fontVariantNumeric: 'tabular-nums' } : {}) }}>
                    {id === 'pageName' && !r.pageName
                      ? <span style={{ fontStyle: 'italic', color: 'var(--color-muted-foreground)' }}>{t('deleted')}</span>
                      : cell(r, id)}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
        <div style={{ padding: '10px 16px', textAlign: 'center', fontSize: 12, color: 'var(--color-muted-foreground)' }}>
          {loading ? t('loading') : t('count', { n: filtered.length })}
        </div>
      </div>

      {showExport && (
        <ExportModal<AnalyticsRow>
          cols={cols}
          labelFor={(id) => t(COL_LABEL[id])}
          fetchAll={async () => filtered}
          getCell={(r, id) => cell(r, id)}
          filename="page-analytics" sheetName={t('title')} total={filtered.length}
          onClose={() => setShowExport(false)} />
      )}
    </div>
  )
}

import { useState, type CSSProperties } from 'react'

/* ──────────────────────────────────────────────────────────────────────────
 * Briques partagées de l'outil Page Analytics (brique MelisCmsPageAnalytics) :
 * i18n FR/EN, styles inline (variables CSS du thème de l'hôte), icônes SVG,
 * gestionnaire de colonnes (masquer + réordonner) et carte KPI. La brique ne
 * peut PAS importer les modules de l'hôte (Tailwind/shadcn/lucide/i18n).
 * ────────────────────────────────────────────────────────────────────────── */

// ── i18n ──
export type Lang = 'fr' | 'en'
export function currentLang(): Lang {
  return (document.documentElement.lang || 'en').toLowerCase().startsWith('fr') ? 'fr' : 'en'
}
const DICT: Record<Lang, Record<string, string>> = {
  fr: {
    title: 'Site analytics', subtitle: 'Visites par page (suivi intégré Melis)',
    old_hint: 'Outil Melis classique — onglets Analytics et Paramètres (affectation aux sites)',
    site: 'Site', site_all: 'Tous les sites',
    search: 'Rechercher une page…', empty: 'Aucune donnée de visite', count: '{n} pages — fin de la liste',
    kpi_hits: 'Visites', kpi_pages: 'Pages suivies', kpi_sites: 'Sites', kpi_last: 'Dernière visite',
    col_pageId: 'ID page', col_pageName: 'Page', col_count: 'Visites', col_last: 'Dernière visite',
    columns: 'Colonnes', export: 'Exporter', cols_visible: 'Visibles', cols_hidden: 'Masquées', drag_here: 'Glisser ici', reset: 'Réinitialiser',
    details: 'Voir les visites', back: 'Retour', refresh: 'Rafraîchir', loading: 'Chargement…', none: '—',
    deleted: '(page supprimée)', no_access: 'Vous n’avez pas les droits pour consulter cet outil.',
    // drill-down
    detail_title: 'Visites de « {n} »', detail_subtitle: 'Détail des visites individuelles',
    d_col_id: 'ID', d_col_date: 'Date de visite', d_col_session: 'Session', d_search: 'Rechercher une visite…',
    detail_empty: 'Aucune visite', detail_count: '{n} visites — fin de la liste',
    // onglets + paramètres
    tab_analytics: 'Analytics', tab_settings: 'Paramètres',
    set_subtitle: 'Affecter un module analytics à un site et régler ses paramètres',
    set_site: 'Site', set_site_ph: 'Sélectionner un site',
    set_module: 'Module analytics', set_module_ph: 'Sélectionner un module analytics',
    set_module_help: 'Le module choisi fournit l’affichage de l’onglet Analytics pour ce site.',
    set_js: 'Script analytics personnalisé',
    set_js_help: 'JavaScript injecté dans le <head> de toutes les pages du front de ce site.',
    set_js_admin: 'Seul un administrateur de la plateforme peut modifier ce script.',
    set_save: 'Enregistrer', set_saving: 'Enregistrement…',
    set_pick_site: 'Sélectionnez un site pour afficher ses paramètres.',
    set_no_settings: 'Ce module n’a pas de paramètre supplémentaire.',
    set_file_legacy: 'L’envoi de fichier se fait ici comme dans l’outil classique.',
    set_current_file: 'Fichier actuel : {n}',
    set_error: 'Échec de l’enregistrement.',
  },
  en: {
    title: 'Site analytics', subtitle: 'Visits per page (Melis built-in tracking)',
    old_hint: 'Classic Melis tool — Analytics and Settings tabs (assign to sites)',
    site: 'Site', site_all: 'All sites',
    search: 'Search a page…', empty: 'No visit data', count: '{n} pages — end of list',
    kpi_hits: 'Visits', kpi_pages: 'Tracked pages', kpi_sites: 'Sites', kpi_last: 'Last visit',
    col_pageId: 'Page ID', col_pageName: 'Page', col_count: 'Visits', col_last: 'Last visit',
    columns: 'Columns', export: 'Export', cols_visible: 'Visible', cols_hidden: 'Hidden', drag_here: 'Drag here', reset: 'Reset',
    details: 'View visits', back: 'Back', refresh: 'Refresh', loading: 'Loading…', none: '—',
    deleted: '(deleted page)', no_access: 'You do not have permission to view this tool.',
    detail_title: 'Visits of “{n}”', detail_subtitle: 'Individual visit details',
    d_col_id: 'ID', d_col_date: 'Visit date', d_col_session: 'Session', d_search: 'Search a visit…',
    detail_empty: 'No visit', detail_count: '{n} visits — end of list',
    // tabs + settings
    tab_analytics: 'Analytics', tab_settings: 'Settings',
    set_subtitle: 'Assign an analytics module to a site and configure its parameters',
    set_site: 'Site', set_site_ph: 'Select a site',
    set_module: 'Analytics module', set_module_ph: 'Select an analytics module',
    set_module_help: 'The selected module provides the Analytics tab display for this site.',
    set_js: 'Custom analytics script',
    set_js_help: 'JavaScript injected into the <head> of every front page of this site.',
    set_js_admin: 'Only a platform administrator can change this script.',
    set_save: 'Save', set_saving: 'Saving…',
    set_pick_site: 'Select a site to display its settings.',
    set_no_settings: 'This module has no additional parameter.',
    set_file_legacy: 'File upload works here just like in the classic tool.',
    set_current_file: 'Current file: {n}',
    set_error: 'Save failed.',
  },
}
export function useT() {
  const lang = currentLang()
  return (key: string, vars?: Record<string, string | number>) => {
    let s = DICT[lang][key] ?? key
    if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v))
    return s
  }
}

// ── Dates : suivent la langue du BO, pas celle du navigateur ──
export function fmtDate(value: string | null | undefined, lang: Lang): string {
  if (!value) return '—'
  const d = new Date(String(value).replace(' ', 'T'))
  if (isNaN(d.getTime())) return String(value)
  return d.toLocaleString(lang === 'fr' ? 'fr-FR' : 'en-GB', {
    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit',
  })
}

// ── Styles (variables CSS du thème) ──
export const card: CSSProperties = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)' }
export const inputCss: CSSProperties = { height: 40, width: '100%', boxSizing: 'border-box', borderRadius: 8, border: '1px solid var(--color-input,var(--color-border))', background: 'var(--color-card)', color: 'var(--color-foreground)', padding: '0 12px', fontSize: 14, outline: 'none' }
export const btnPrimary: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 14px', borderRadius: 8, border: 0, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', fontSize: 14, fontWeight: 500, cursor: 'pointer' }
export const btnGhost: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 12px', borderRadius: 8, border: '1px solid var(--color-border)', background: 'var(--color-card)', color: 'var(--color-foreground)', fontSize: 14, cursor: 'pointer' }
export const iconBtn: CSSProperties = { display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 28, height: 28, borderRadius: 6, border: 0, background: 'transparent', color: 'var(--color-muted-foreground)', cursor: 'pointer' }
export const th: CSSProperties = { textAlign: 'left', padding: '10px 16px', fontSize: 11, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '.04em', color: 'var(--color-muted-foreground)', whiteSpace: 'nowrap' }
export const td: CSSProperties = { padding: '10px 16px', fontSize: 14, color: 'var(--color-foreground)', borderTop: '1px solid var(--color-border)' }
export const pageWrap: CSSProperties = { display: 'flex', flexDirection: 'column', gap: 20, padding: 24, height: '100%', boxSizing: 'border-box', overflow: 'auto' }

// ── Icônes ──
const sIcon = { width: 15, height: 15, flexShrink: 0 } as const
export const EyeIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
export const GripIcon = () => <svg style={{ width: 13, height: 13, flexShrink: 0, color: 'var(--color-muted-foreground)' }} viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
export const ChartIcon = () => <svg style={{ width: 16, height: 16, flexShrink: 0 }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 3v18h18" /><rect x="7" y="10" width="3" height="7" /><rect x="12" y="6" width="3" height="11" /><rect x="17" y="13" width="3" height="4" /></svg>

// ── KPI ──
export function Kpi({ label: lbl, value }: { label: string; value: string | number | null }) {
  return (
    <div style={{ ...card, display: 'flex', flexDirection: 'column', gap: 2, padding: 16, flex: 1, minWidth: 140 }}>
      <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{lbl}</span>
      <span style={{ fontSize: 22, fontWeight: 700 }}>{value == null ? '…' : value}</span>
    </div>
  )
}

// ── Gestionnaire de colonnes (masquer + réordonner, persisté) ──
export type ColDef = { id: string; visible: boolean }
export const visibleCols = (c: ColDef[]) => c.filter((x) => x.visible)
export function makeColStore(key: string, defaults: ColDef[]) {
  const load = (): ColDef[] => {
    try {
      const raw = localStorage.getItem(key)
      if (!raw) return defaults
      const saved: ColDef[] = JSON.parse(raw)
      const ordered = saved.map((s) => { const d = defaults.find((c) => c.id === s.id); return d ? { id: d.id, visible: s.visible } : null }).filter(Boolean) as ColDef[]
      const missing = defaults.filter((d) => !saved.find((s) => s.id === d.id))
      return [...ordered, ...missing]
    } catch { return defaults }
  }
  const save = (c: ColDef[]) => { try { localStorage.setItem(key, JSON.stringify(c)) } catch { /* */ } }
  return { load, save, defaults }
}

const panelCss: CSSProperties = { display: 'flex', flexDirection: 'column', gap: 2, minHeight: 130, maxHeight: 'min(48vh, 320px)', overflowY: 'auto', minWidth: 0, borderRadius: 8, border: '1px dashed var(--color-border)', padding: 6 }
const panelTitle: CSSProperties = { padding: '0 6px 4px', fontSize: 10, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '.06em', color: 'var(--color-muted-foreground)' }

export function ColManager({ cols, labelFor, onChange, onSave, defaults, onClose }: {
  cols: ColDef[]; labelFor: (id: string) => string; onChange: (c: ColDef[]) => void
  onSave: (c: ColDef[]) => void; defaults: ColDef[]; onClose: () => void
}) {
  const t = useT()
  const [dragId, setDragId] = useState<string | null>(null)
  const [over, setOver] = useState<{ id: string; panel: 'visible' | 'hidden' } | null>(null)
  const shown = cols.filter((c) => c.visible)
  const hidden = cols.filter((c) => !c.visible)

  function drop(panel: 'visible' | 'hidden') {
    if (!dragId) return
    const src = cols.find((c) => c.id === dragId)!
    const upd = { ...src, visible: panel === 'visible' }
    let vList = shown.filter((c) => c.id !== dragId)
    const hList = hidden.filter((c) => c.id !== dragId)
    if (panel === 'visible') {
      const dst = over?.id
      if (!dst || dst === '__panel__') vList = [...vList, upd]
      else { const i = vList.findIndex((c) => c.id === dst); vList = i === -1 ? [...vList, upd] : [...vList.slice(0, i), upd, ...vList.slice(i)] }
      const next = [...vList, ...hList]; onChange(next); onSave(next)
    } else { const next = [...vList, ...hList, upd]; onChange(next); onSave(next) }
    setDragId(null); setOver(null)
  }

  function item(col: ColDef, panel: 'visible' | 'hidden') {
    const isOver = over?.id === col.id && over?.panel === panel
    return (
      <div key={col.id} draggable
        onDragStart={() => setDragId(col.id)} onDragEnd={() => { setDragId(null); setOver(null) }}
        onDragOver={(e) => { e.preventDefault(); e.stopPropagation(); if (over?.id !== col.id || over?.panel !== panel) setOver({ id: col.id, panel }) }}
        onDrop={(e) => { e.preventDefault(); drop(panel) }}
        style={{ display: 'flex', alignItems: 'center', gap: 8, borderRadius: 8, padding: '6px 8px', fontSize: 14, cursor: 'grab', userSelect: 'none', opacity: dragId === col.id ? 0.4 : 1, background: isOver ? 'color-mix(in srgb, var(--color-primary) 12%, transparent)' : 'transparent', boxShadow: isOver ? '0 0 0 1px color-mix(in srgb, var(--color-primary) 35%, transparent)' : 'none' }}>
        <GripIcon /><span style={{ flex: 1, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{labelFor(col.id)}</span>
      </div>
    )
  }

  return (
    <div style={{ ...card, position: 'absolute', right: 0, top: '100%', marginTop: 6, zIndex: 50, width: 380, maxWidth: 'calc(100vw - 1rem)' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 12px', borderBottom: '1px solid var(--color-border)' }}>
        <span style={{ fontSize: 14, fontWeight: 600 }}>{t('columns')}</span>
        <button style={{ ...iconBtn, width: 22, height: 22 }} onClick={onClose}>✕</button>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, padding: 12 }}>
        <div style={panelCss}
          onDragOver={(e) => { e.preventDefault(); if (over?.id !== '__panel__' || over?.panel !== 'hidden') setOver({ id: '__panel__', panel: 'hidden' }) }}
          onDrop={(e) => { e.preventDefault(); drop('hidden') }}>
          <p style={panelTitle}>{t('cols_hidden')}</p>
          {hidden.length === 0 ? <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, color: 'var(--color-muted-foreground)', opacity: 0.5, padding: '16px 0' }}>{t('drag_here')}</div> : hidden.map((c) => item(c, 'hidden'))}
        </div>
        <div style={panelCss}
          onDragOver={(e) => { e.preventDefault(); if (over?.id !== '__panel__' || over?.panel !== 'visible') setOver({ id: '__panel__', panel: 'visible' }) }}
          onDrop={(e) => { e.preventDefault(); drop('visible') }}>
          <p style={panelTitle}>{t('cols_visible')}</p>
          {shown.length === 0 ? <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, color: 'var(--color-muted-foreground)', opacity: 0.5, padding: '16px 0' }}>{t('drag_here')}</div> : shown.map((c) => item(c, 'visible'))}
        </div>
      </div>
      <div style={{ borderTop: '1px solid var(--color-border)', padding: 6 }}>
        <button style={{ ...btnGhost, width: '100%', height: 30, border: 0, justifyContent: 'center', color: 'var(--color-muted-foreground)' }}
          onClick={() => { onChange(defaults); onSave(defaults) }}>{t('reset')}</button>
      </div>
    </div>
  )
}

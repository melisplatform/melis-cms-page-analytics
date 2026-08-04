/* Client fetch typé de l'API Page Analytics (lecture seule).
 * Endpoints servis par MelisCmsPageAnalytics\Controller\MelisReactApiPageAnalyticsController
 * sous /melis/react-api/page-analytics/*. Format : { success, data, error }. */

const XHR_HEADER = { 'X-Requested-With': 'XMLHttpRequest' } as const

async function apiGet<T>(url: string): Promise<T> {
  const res = await fetch(url, { headers: { ...XHR_HEADER }, credentials: 'include' })
  const json = (await res.json()) as { success: boolean; data?: T; error?: string }
  if (!json.success) throw new Error(json.error || `HTTP ${res.status}`)
  return json.data as T
}

export interface AnalyticsRow {
  pageId: number
  pageName: string
  count: number
  lastVisit: string | null
}

export interface AnalyticsStats {
  hits: number
  pages: number
  sites: number
  lastVisit: string | null
}

export interface SiteOption {
  id: number
  name: string
}

export interface ListParams {
  limit?: number
  search?: string
  site?: number
  sort?: string
  dir?: 'asc' | 'desc'
  after?: string | null
}

export interface ListResult<T> {
  items: T[]
  total: number
  nextCursor: string | null
}

function qs(params: Record<string, string | number | undefined | null>): string {
  const sp = new URLSearchParams()
  for (const [k, v] of Object.entries(params)) {
    if (v !== undefined && v !== null && v !== '' && v !== 0) sp.set(k, String(v))
  }
  const s = sp.toString()
  return s ? `?${s}` : ''
}

export function fetchAnalytics(p: ListParams = {}): Promise<ListResult<AnalyticsRow>> {
  // `after` est une chaîne base64 non vide (jamais falsy quand un curseur existe) → qs() la conserve.
  return apiGet(`/melis/react-api/page-analytics${qs({ limit: p.limit, search: p.search, site: p.site, sort: p.sort, dir: p.dir, after: p.after })}`)
}

export function fetchAnalyticsStats(p: { search?: string; site?: number } = {}): Promise<AnalyticsStats> {
  return apiGet(`/melis/react-api/page-analytics/stats${qs({ search: p.search, site: p.site })}`)
}

export function fetchAnalyticsSites(): Promise<{ sites: SiteOption[] }> {
  return apiGet('/melis/react-api/page-analytics/sites')
}

/* ── Onglet « Paramètres » ─────────────────────────────────────────────────────
 * LECTURE : endpoint React de ce module (schéma + valeurs, data-driven).
 * ÉCRITURE : action LEGACY `.../MelisCmsPageAnalyticsTool/save` en FormData — elle porte toute
 * la logique métier (validation Laminas, upload de clé privée, sérialisation, garde admin sur le
 * JS brut, flash messenger) et renvoie déjà `{success, textTitle, textMessage, errors}`.
 * On ne la duplique donc PAS côté React : les deux vues restent strictement équivalentes. */

export interface AnalyticsModuleOption {
  key: string
  label: string
  /** le module déclare un formulaire de réglages propre (hors sélecteurs de l'outil) */
  settings: boolean
}

export interface SettingsField {
  name: string
  label: string
  tooltip: string
  type: 'text' | 'textarea' | 'select' | 'password' | 'file'
  required: boolean
  options: { value: string; label: string }[]
}

export interface AnalyticsSettings {
  siteId: number
  modules: AnalyticsModuleOption[]
  /** module actuellement affecté au site ('' si aucun) */
  analyticsKey: string
  /** module dont `fields`/`values` sont renvoyés */
  selectedKey: string
  fields: SettingsField[]
  values: Record<string, string>
  jsAnalytics: string
  /** pads_js_analytics = JS injecté dans le <head> du front → admin plateforme uniquement */
  jsEditable: boolean
}

export function fetchAnalyticsSettings(siteId: number, key?: string): Promise<AnalyticsSettings> {
  return apiGet(`/melis/react-api/page-analytics/settings${qs({ site: siteId, key })}`)
}

/** Réponse standard d'une action outil Melis. */
export interface SaveResult {
  success: number
  textTitle: string
  textMessage: string
  errors: Record<string, Record<string, string>>
}

const LEGACY_SAVE_URL = '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save'

export async function saveAnalyticsSettings(form: FormData): Promise<SaveResult> {
  const res = await fetch(LEGACY_SAVE_URL, {
    method: 'POST',
    headers: { ...XHR_HEADER }, // pas de Content-Type : le navigateur pose le boundary multipart
    credentials: 'include',
    body: form,
  })
  return (await res.json()) as SaveResult
}

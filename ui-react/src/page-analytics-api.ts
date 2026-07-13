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
  page?: number
  limit?: number
  search?: string
  site?: number
  sort?: string
  dir?: 'asc' | 'desc'
}

export interface ListResult<T> {
  items: T[]
  total: number
  page: number
  limit: number
}

function qs(params: Record<string, string | number | undefined>): string {
  const sp = new URLSearchParams()
  for (const [k, v] of Object.entries(params)) {
    if (v !== undefined && v !== '' && v !== 0) sp.set(k, String(v))
  }
  const s = sp.toString()
  return s ? `?${s}` : ''
}

export function fetchAnalytics(p: ListParams = {}): Promise<ListResult<AnalyticsRow>> {
  return apiGet(`/melis/react-api/page-analytics${qs({ page: p.page, limit: p.limit, search: p.search, site: p.site, sort: p.sort, dir: p.dir })}`)
}

export function fetchAnalyticsStats(p: { search?: string; site?: number } = {}): Promise<AnalyticsStats> {
  return apiGet(`/melis/react-api/page-analytics/stats${qs({ search: p.search, site: p.site })}`)
}

export function fetchAnalyticsSites(): Promise<{ sites: SiteOption[] }> {
  return apiGet('/melis/react-api/page-analytics/sites')
}

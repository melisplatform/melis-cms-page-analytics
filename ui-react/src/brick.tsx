import PageAnalyticsPage from './PageAnalyticsPage'

/**
 * Brick entry point. Registers the Page Analytics tool page with the MelisCore React shell.
 * React / ReactRouter are EXTERNAL (host globals) so hooks/Router/context are shared.
 */
declare global {
  interface Window {
    __melisRegisterBrick?: (b: { id: string; Component: unknown }) => void
  }
}

window.__melisRegisterBrick?.({ id: 'pageanalytics', Component: PageAnalyticsPage })

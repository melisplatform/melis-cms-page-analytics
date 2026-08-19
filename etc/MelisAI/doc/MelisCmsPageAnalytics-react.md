---
title: MelisCmsPageAnalytics module — React back-office
package: melisplatform/melis-cms-page-analytics
doc_type: module-documentation-react
audience: [users, developers, ai]
language: en
module_version: unversioned
last_reviewed: 2026-08-19
maintainer: Melis Technology
keywords: [page-analytics, visits, page-hits, google-analytics, react, brick, back-office, react-api, capabilities, melis, cms, new-old-toggle, site-level-display]
screenshots_dir: ./images/react
related_docs: [./MelisCmsPageAnalytics.md]
---

# MelisCmsPageAnalytics (React back-office) — Functional & Technical Documentation (for AI)

> **What this is.** MelisCmsPageAnalytics is the **basic page-visit analytics** system of Melis:
> a built-in counter records a hit each time a published front page is rendered, and a site can
> plug in an **external analytics provider** (e.g. Google Analytics) whose JS is injected into the
> page `<head>`. This document covers it **in the new React back-office** (`/melis-react`): the
> module ships a **native full-React brick** — a real read-only React UI (visits table + provider
> settings) calling a `react-api` JSON layer — with a **New / Old toggle** that can fall back to
> the legacy tool in an iframe. For the underlying data model, listeners, providers and services
> see the [legacy tool doc](./MelisCmsPageAnalytics.md); this doc does not repeat them.
>
> **⚠ React screenshots are not available yet.** The `etc/MelisAI/doc/images/react/` folder is
> empty, so this document ships **without inline images and without a Screenshot index**. When the
> React screens are captured, save them under `./images/react/` and add the index back.
>
> **How this document is organised — two clearly separated parts:**
> - **[Part A — Functional Guide](#part-a--functional-guide)** — for everyday users (and the
>   chat assistant) using the React back-office. Plain language.
> - **[Part B — Technical Reference](#part-b--technical-reference)** — for developers and AI
>   building inside the React UI, with code (brick manifest, endpoints, capabilities).
>
> **Audience**: consumed by the **MelisAI** MCP. **Status**: reviewed 2026-08-19.

---

## 0. Where this lives in the React back-office — read this first

- **Brick kind: native full-React** (not an iframe brick). The UI is authored in React
  (`ui-react/src/`) and reads through `/melis/react-api/page-analytics…` endpoints defined in the
  module. It keeps a **New / Old toggle**: *Old* renders the legacy tool in an iframe
  (`/melis/react-tool-page?key=meliscms_page_analytics_display`), *New* is the React UI (default).
- **Where in the menu.** Sidebar → **Marketing / Site Tools** group → **Page Analytics** (the
  brick `route` is `/melis-marketing/meliscms-page-analytics`). The tool appears **only if the
  module is activated** (modular brick discovery, see §B5).
- **Two native tabs inside the tool** (mirroring the legacy tool): **Analytics** (visits table)
  and **Settings** (provider selection + settings + custom JS). No drill-down — the visits view is
  read-only, faithful to the legacy.
- **Read-only by design.** The visits data is never mutated from React. The only write path is the
  **Settings** save, which POSTs to the **legacy tool action** (`.../MelisCmsPageAnalyticsTool/save`)
  so all business logic (Laminas validation, GA private-key upload, `pads_settings` serialization,
  platform-admin guard on the raw JS, flash messenger) stays server-side.
- **Modular site-level display.** When a site is assigned a third-party analytics module that
  declares a display (e.g. **MelisCmsGoogleAnalytics** via `react_display_key`), the Analytics tab
  **hosts that module's own React display** (registered on the global
  `window.__melisAnalyticsSiteDisplays` registry) instead of the native visits table — falling back
  to a legacy iframe if no React component is registered (see §B5).
- **CMS page tab.** The module also contributes a **Page Analytics** tab to the CMS page editor
  (capability `meliscms_page_analytics_tab` under `meliscms_page`), backed by its own react-api
  endpoint (§B3). Cross-reference: [MelisCmsPageAnalytics.md](./MelisCmsPageAnalytics.md).

---
---

# PART A — Functional Guide

## A1. What you can do with Page Analytics in the new back-office

- **See how your pages are doing** — a table of recorded visits **aggregated per page**: page id,
  page name, visit count and last visit, with a **site filter**, KPI cards, search, column manager
  and **Export**.
- **Choose an analytics provider per site** — in the **Settings** tab, pick which analytics module
  a site uses (built-in counter, Google Analytics, …) and fill its settings + an optional custom
  `<head>` JS snippet.
- **Compare New vs Old** — switch the whole tool between the React UI and the classic tool with the
  **New / Old** toggle.
- **Read per-page visits in the CMS page editor** — the **Page Analytics** tab on a page shows the
  visits recorded for *that* page.

> No setup is needed for the built-in counter — it records visits automatically. See the
> [legacy doc §A2](./MelisCmsPageAnalytics.md) for exactly what is (and isn't) counted.

## A2. Finding it in /melis-react

**Where:** left sidebar → **Site Tools / Marketing** → **Page Analytics**. It opens as a top tab
named **Page Analytics**, with a persistent header (title + **New / Old** toggle) and two native
tabs underneath: **Analytics** and **Settings**.

*(React screenshots are not available yet — see the intro note.)*

## A3. Key words explained

- **Visit / hit** — one recorded page view (deduped per page, per session, per day by the built-in
  counter; see the [legacy doc §A2](./MelisCmsPageAnalytics.md)).
- **Provider / analytics module** — the back-end that handles analytics for a site: the built-in
  counter, or a third-party module such as Google Analytics.
- **New / Old** — the two views of the same tool: **New** = React UI, **Old** = the classic tool in
  an iframe.
- **Site-level display** — when a third-party provider ships its own React dashboard, the Analytics
  tab shows *that* dashboard for the selected site instead of the built-in visits table.

> For the domain glossary and the data model, see the [legacy doc](./MelisCmsPageAnalytics.md).

## A4. The Analytics tab — visits per page

The default view. Pick a **site** (or *All sites*), then read the table: **Page id**, **Page name**,
**Visits** (count) and **Last visit**. Above it, **KPI cards** show total **hits**, distinct
**pages**, distinct **sites** and the **last visit**. A **search** box filters by page id / name;
a **Columns** manager hides/reorders columns; **Export** downloads the list; the **↻** button
refreshes. The list uses infinite scroll and server-side sort (click a column header to sort).

Deleted pages show an italic *"(deleted)"* label in place of the missing name.

> **Read-only:** this tab never changes data — it only reads recorded hits.

If the selected site is assigned a **third-party analytics module** that provides its own display
(e.g. Google Analytics), the tab instead shows **that module's dashboard** for the site (KPIs,
search, columns and export are hidden — the module renders its own).

## A5. The Settings tab — choose a provider per site

Pick a **Site**, then an **Analytics module** (built-in "no analytics" option + every active
provider). If the chosen module declares its own settings, its fields appear (text / textarea /
select / password / **file** upload, e.g. a Google Analytics private key). Platform admins can also
edit a **custom JS snippet** injected into every front page's `<head>`.

**Save** persists the choice + settings. The two selectors (Site + Module) are always visible;
the module's own settings and the Save button only appear once a site is chosen.

> **Tip:** the custom JS field is **admin-only** — non-admins see it read-only. Saving posts to the
> same legacy action the classic tool uses, so New and Old stay strictly equivalent.

## A6. The CMS page editor tab — one page's visits

Open a page in the CMS editor: a **Page Analytics** tab summarises the visits recorded **for that
page** (total visits, distinct sessions, last visit, and a paginated list of recent visit dates).

## A7. Common tasks — "How do I…?"

- **See my pages' traffic** → Page Analytics → **Analytics** tab → pick a site (or *All sites*).
- **Filter to one site** → the site selector at the top of the Analytics tab.
- **Export the visits** → **Export** button on the Analytics tab.
- **Switch a site to Google Analytics** → **Settings** tab → pick the site → choose the GA module →
  fill its settings → **Save** (install the GA module first).
- **Compare with the classic tool** → top-right **New / Old** toggle → **Old**.
- **Check one page's visits** → open the page in the CMS editor → **Page Analytics** tab.

---
---

# PART B — Technical Reference

## B1. React presence at a glance

| Item | Value |
|---|---|
| Brick kind | **Native full-React** (with a New/Old legacy-iframe fallback) |
| Brick id | `pageanalytics` (matches `brick.tsx` ⇄ `brick.manifest.json`) |
| Manifest `route` | `/melis-marketing/meliscms-page-analytics` |
| `label` | `Page Analytics` |
| `forwardKey` | `MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool` |
| `melisKey` (manifest / Old-view iframe) | `meliscms_page_analytics_display` |
| `entry` | `brick.js` |
| `subTabs` | *(not set)* — tabs are internal React state, not host sub-tabs |
| `persistent` | `true` (state + iframe kept mounted across tool switches) |
| Access-guard melisKey (controller) | `meliscms_page_analytics_tools_section` (rights-bearing node) |
| API base | `/melis/react-api/page-analytics` |
| Tables (read) | `melis_cms_page_analytics`, `melis_cms_page_published`, `melis_cms_site`, `melis_cms_page_analytics_data` — see [legacy doc §B3](./MelisCmsPageAnalytics.md) |
| Activation-gated | Yes (appears iff the module is in `config/melis.module.load.php`) |

## B2. The brick — anatomy

Source in `ui-react/` (Vite **IIFE**, React externalised to the host globals `MelisReact*`,
output to `public/ui-react/brick.js` next to `brick.manifest.json` — see `ui-react/vite.config.ts`,
name `MelisCmsPageAnalyticsBrick`).

`ui-react/src/brick.tsx` registers ONE routed component under the brick id:
```tsx
import PageAnalyticsPage from './PageAnalyticsPage'
window.__melisRegisterBrick?.({ id: 'pageanalytics', Component: PageAnalyticsPage }) // id MUST match the manifest
```

Manifest (`public/ui-react/brick.manifest.json`):
```json
{ "id": "pageanalytics", "route": "/melis-marketing/meliscms-page-analytics",
  "label": "Page Analytics", "forwardKey": "MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool",
  "melisKey": "meliscms_page_analytics_display", "entry": "brick.js", "persistent": true }
```

React components (`ui-react/src/`):

| File | Role |
|---|---|
| `PageAnalyticsPage.tsx` | Container mounted on the "Page Analytics" tab. Owns the **New/Old** `mode`, the two native tabs (`analytics` / `settings`), and the `AnalyticsList` (visits table). Renders the **Old-view iframe** `/melis/react-tool-page?key=meliscms_page_analytics_display`. Hosts the **modular site-level display** logic (`useSiteDisplayComp`, `window.__melisAnalyticsSiteDisplays`). |
| `SettingsPanel.tsx` | The **Settings** tab — Site + Module selectors, data-driven module fields, custom JS textarea (admin-only). Reads via `fetchAnalyticsSettings`; **writes via the legacy save action** (`saveAnalyticsSettings`, FormData). No business logic client-side. |
| `ExportModal.tsx` | The list Export (cursor walk of the whole result set in batches of 100). |
| `ViewToggle.tsx` | The reusable **New (React) / Old (iframe)** toggle (`type ViewMode = 'react' \| 'iframe'`, `compact` mode for narrow viewports). |
| `page-analytics-api.ts` | The API client (see §B3) + the TS response shapes (`AnalyticsRow`, `AnalyticsStats`, `SiteOption`, `AnalyticsSettings`, `SettingsField`). |
| `ui.tsx` | Self-contained i18n (fr/en from `document.documentElement.lang`), inline styles (theme CSS vars), SVG icons, KPI card, column manager (`makeColStore`, `ColManager`). |
| `use-keyset-list.ts` | Keyset list hook (infinite scroll + server-side sort) driving the visits table. |
| `shared/` | `useIsNarrow.ts` (responsive), `ExpandableRow.tsx` (mobile column reveal), `melis-form-errors.tsx` (settings error banner / notify), `use-drag-reorder.ts` (shared util). |

> **Brick constraint:** the bundle externalises only `react`/`react-dom`/`react/jsx-runtime`/
> `react-router-dom` to the host globals; it cannot import host modules (Tailwind/shadcn/lucide/i18n),
> hence inline styles + in-file i18n.

## B3. React API — endpoints

Routes live in **`config/react-api.php`** (merged into the module via
`MelisCmsPageAnalytics\Module::getConfig()` with `ArrayUtils::merge`). Two controllers:

- **`MelisReactApiPageAnalyticsController`** (invokable alias
  `MelisCmsPageAnalytics\Controller\MelisReactApiPageAnalytics`) — the **tool** (read + settings
  read). All under `/melis/react-api/page-analytics`.
- **`MelisReactApiPageAnalyticsTabController`** (alias `…\MelisReactApiPageAnalyticsTab`) — the
  **CMS page editor tab** endpoint at `/melis/react-api/cms-page/analytics`.

Contract `{ success, data, error }`; every fetch sends `X-Requested-With: XMLHttpRequest` +
`credentials:'include'`.

| Method & URL | Controller · action | Purpose |
|---|---|---|
| `GET /page-analytics` | `PageAnalytics · list` | Visits **aggregated per page** (keyset: `limit`, `search`, `site`, `sort`, `dir`, `after`) → `{items,total,nextCursor}` where each item is `{pageId,pageName,count,lastVisit}` |
| `GET /page-analytics/stats` | `PageAnalytics · stats` | KPI `{hits, pages, sites, lastVisit}` (honours `search`/`site`) |
| `GET /page-analytics/sites` | `PageAnalytics · sites` | Site options `{sites:[{id,name}]}` for the selector |
| `GET /page-analytics/settings` | `PageAnalytics · settings` | Settings-tab state (read): `{siteId, modules[], analyticsKey, selectedKey, fields[], values, jsAnalytics, jsEditable}` — module list + form schema, data-driven |
| `GET /cms-page/analytics` | `PageAnalyticsTab · get` | One page's visits (`idPage`, `page`, `perPage`) → `{visits, sessions, lastVisit, recent:[{date}], page, perPage, recentTotal}` |

Example (from `page-analytics-api.ts`):
```ts
const XHR = { 'X-Requested-With': 'XMLHttpRequest' }
// list — aggregated per page
await fetch(`/melis/react-api/page-analytics?limit=25&site=1&sort=count&dir=desc`,
  { headers: XHR, credentials: 'include' })
// KPI stats
await fetch(`/melis/react-api/page-analytics/stats?site=1`, { headers: XHR, credentials: 'include' })
// settings-tab state for a site
await fetch(`/melis/react-api/page-analytics/settings?site=1`, { headers: XHR, credentials: 'include' })
```

**Writing settings** is NOT a react-api route — it POSTs to the **legacy tool action** so the
server-side logic is reused verbatim:
```ts
const LEGACY_SAVE_URL = '/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save'
await fetch(LEGACY_SAVE_URL, { method: 'POST', headers: XHR, credentials: 'include', body: formData })
// → { success, textTitle, textMessage, errors }
```

> **Note on the data layer.** The tool controller reads the tables **directly via parameterised
> SQL** (`Laminas\Db\Adapter\AdapterInterface`): the visits table `melis_cms_page_analytics` joined
> to `melis_cms_page_published` (page names) and `melis_cms_site` (site selector), with an opaque
> base64 keyset cursor (aggregated `HAVING` because the sort columns are `COUNT`/`MAX` aggregates).
> The Settings state comes from `MelisCoreConfig` (`meliscms/datas/page_analytics`,
> `meliscms/forms/<key>_settings_form`) + `MelisCmsPageAnalyticsDataTable`, so a third-party module
> (e.g. GA) is picked up with no code change here. The higher-level provider services
> (`MelisCmsPageAnalyticsService`, the built-in recorder) documented in the
> [legacy doc §B2/§B4](./MelisCmsPageAnalytics.md) are not called by these controllers.

## B4. Capabilities (advanced rights)

`config/react.capabilities.php` does **not** declare tool capabilities for the Page Analytics tool
itself. Instead it **contributes one tab** to the CMS page editor, under the shared rights-bearing
node **`meliscms_page`** (the "Edition de page" node), so the tab is gatable in Users → Rights:

```php
'melisReactToolCapabilities' => [
  'meliscms_page' => [
    'tabs' => [
      ['key' => 'meliscms_page_analytics_tab', 'label' => 'tr_melis_cms_page_analytics_title'],
    ],
  ],
],
```

`ArrayUtils::merge` appends this tab alongside MelisCms / SmallBusiness tabs on `meliscms_page`.
The `key` `meliscms_page_analytics_tab` **is** the capability string used to gate the tab in the
React page editor.

The **tool's** react-api controller guards access differently — with an **access check** on the
rights-bearing menu node, not a capability:
```php
private const MELIS_KEY = 'meliscms_page_analytics_tools_section';
if ($deny = $this->denyUnlessAccess()) { return $deny; } // auth + MelisCoreRights::canAccess(MELIS_KEY) → 401/403
```
> ⚠ Do **not** guard on `meliscms_page_analytics_display` (the manifest `melisKey`): that is the
> renderable **zone** key (the Old-view iframe target), not a granted rights node — guarding on it
> would 403 every request. The controller comment says so explicitly.

## B5. Host integration

- **Discovery / gating.** `GET /melis/react-api/react-modules` lists active modules that ship a
  `brick.manifest.json`; the host (`melis-core/ui-react/src/lib/bricks.ts`) loads `brick.js` (shared
  React globals) and mounts the brick. Removing `MelisCmsPageAnalytics` from
  `config/melis.module.load.php` makes it disappear.
- **Menu → route.** `useNavMenu` maps the `forwardKey` `MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool`
  to the tree route; `Component: PageAnalyticsPage` renders there.
- **New/Old toggle.** `PageAnalyticsPage` keeps a persistent header; *Old* mounts the legacy iframe
  `/melis/react-tool-page?key=meliscms_page_analytics_display` (`MelisReactOverride`), lazily created
  on first switch and kept mounted (the brick is `persistent`).
- **Modular site-level display (host side).** This brick **consumes** a global registry:
  `window.__melisAnalyticsSiteDisplays[<analyticsKey>]`. Another module's brick (e.g.
  **MelisCmsGoogleAnalytics**) registers its own React site-level display there; `PageAnalyticsPage`
  looks it up by the site's assigned `analyticsKey`, re-checks on the
  `melis:analytics-site-display-registered` window event (bundles load asynchronously), and mounts it
  in place of the native visits table. If nothing is registered after a short guard delay, it falls
  back to an **iframe** `/melis/react-tool-page?key=<displayKey>&siteId=<site>` (the module's
  `react_display_key` from `meliscms/datas/page_analytics`).
- **CMS page tab.** The tab's data comes from `/melis/react-api/cms-page/analytics`; the tab itself
  is gated by the `meliscms_page_analytics_tab` capability under `meliscms_page` (§B4) — the shared
  page-editor tab mechanism (`meliscms_tabs`) surfaces the button.
- **i18n.** The brick reads the active language from `document.documentElement.lang` (session locale,
  set by the host `I18nProvider`) and ships an in-file `{fr,en}` dictionary (`ui.tsx`).
- **Generic bits stay in `melis-react-api`.** The capability resolver is generic (always loaded);
  the tool's controllers/routes/caps live **in this module** (modularity rule).

## B6. Quick code map

```
melis-cms-page-analytics/
├── config/
│   ├── react-api.php            routes (/melis/react-api/page-analytics… + /cms-page/analytics)
│   │                            + invokables → MelisReactApiPageAnalytics / …Tab
│   └── react.capabilities.php   contributes tab meliscms_page_analytics_tab under meliscms_page
├── src/Controller/
│   ├── MelisReactApiPageAnalyticsController.php      list/stats/sites/settings, denyUnlessAccess, direct SQL
│   └── MelisReactApiPageAnalyticsTabController.php   CMS page tab: one page's visits (paginated)
├── ui-react/                    Vite IIFE brick (React external)
│   ├── vite.config.ts           → ../public/ui-react/brick.js (name MelisCmsPageAnalyticsBrick)
│   └── src/  brick.tsx (registers id 'pageanalytics') · PageAnalyticsPage (tabs + Old iframe +
│            site-level display host) · SettingsPanel (legacy save) · ExportModal · ViewToggle
│            · page-analytics-api.ts · ui.tsx · use-keyset-list.ts
│            · shared/{useIsNarrow,ExpandableRow,melis-form-errors,use-drag-reorder}
├── public/ui-react/             brick.js (built) + brick.manifest.json (id/route/label/forwardKey/melisKey)
└── etc/MelisAI/doc/             MelisCmsPageAnalytics.md (legacy) · MelisCmsPageAnalytics-react.md (this)
                                 · images/react/ (empty — no React screenshots yet)
```

> Business logic stays server-side (the built-in counter + provider contract + settings save):
> [MelisCmsPageAnalytics.md](./MelisCmsPageAnalytics.md). React = presentation + API calls.

---

*Document for AI consumption (MelisAI MCP) — React back-office of `melisplatform/melis-cms-page-analytics`.
Part A = functional guide for users; Part B = technical reference with examples for developers/AI.
Legacy tool doc: [./MelisCmsPageAnalytics.md](./MelisCmsPageAnalytics.md). React screenshots not
available yet (no Screenshot index). Last reviewed 2026-08-19.*

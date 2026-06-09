# MelisCmsPageAnalytics — AI & developer guide

> **Module:** `melisplatform/melis-cms-page-analytics` · **Namespace:** `MelisCmsPageAnalytics` · `melis-site: false`
> **What it is:** **basic page-visit analytics** for CMS sites. It does two things: (1) a built-in counter
> records a visit every time a **published front-office page** is rendered, and (2) it lets a site plug in an
> **external analytics provider** (e.g. Google Analytics) by injecting that provider’s JS tag into the page
> `<head>`. Results are shown in a back-office **Page Analytics** tool and as a **tab on each CMS page**. No
> screenshots are bundled with this doc.

---

## 0. The idea — a pluggable analytics framework

The module is built around a tiny **provider contract** so a site can use *any* analytics back-end:

```php
interface MelisCmsPageAnalyticsServiceInterface {
    public function getAnalytics($siteId);   // the whole contract
}
```

- **`MelisCmsDefaultPageAnalyticsService`** is the **built-in provider** — it records visits into this
  module’s own tables (no third party needed).
- A site **chooses** which analytics provider to use in the tool’s **Settings** tab; the choice + that
  provider’s settings (incl. a JS snippet) are stored per-site.
- An **optional Google Analytics module** can register a `GoogleAnalyticsAPIService`; when it is present and
  configured for the site, the built-in counter **steps aside** and GA takes over (see §B2). That module is
  *not* in this repo — this module only defines the contract and the default implementation.

It plugs into the platform at two layers:

| Layer | Where | Doc |
|---|---|---|
| **Front office** | two listeners on the page-render pipeline (count a visit + inject the analytics JS). | [`MelisCms`](../../../melis-cms/etc/MelisAI/doc/MelisCms.md) / MelisFront / MelisEngine |
| **Back office** | a **Page Analytics** Site-Tools tool **and** an **analytics tab on every CMS page**. | [`MelisCore`](../../../melis-core/etc/MelisAI/doc/MelisCore.md) |

**Mental model:** *visitor loads a published page → the front listener records one deduped hit (and/or injects
the provider’s JS) → the BO tool and the per-page tab read those hits back via `getAnalytics($siteId)`.*

---

# PART A — Functional guide

## A1. What you get

- **A site-wide tool** in the left menu under **Site Tools → Page Analytics** (icon `fa-bar-chart`), with two
  tabs:
  - **Site analytics** — a table of recorded visits across the site: **Id**, **Page id**, **Page name**,
    **Date visited**.
  - **Settings** — pick the **analytics provider** for this site and enter its settings (e.g. a Google
    Analytics tracking snippet / view id).
- **A per-page “Page Analytics” tab** — open any page in the CMS and a *Page Analytics* tab (icon *stats*)
  shows the visits recorded **for that page** by date.

## A2. What the built-in counter records (and what it doesn’t)

When the default provider is active, each front-office page view writes one row capturing the **page**, a
**session id** (the `PHPSESSID` cookie), the **visit date/time**, and the **site**. Deliberately:

- **no IP address is stored** (privacy by design — the code comment says so explicitly);
- a visit is recorded **at most once per page, per session, per day** (repeat views the same day are ignored);
- only **active / published** pages are counted (drafts and back-office previews are not).

So the built-in numbers are closer to “unique daily page visits per session” than raw hit counts — keep that
in mind when reading the table.

## A3. How do I…?

- **…just see how my pages are doing?** Open **Site Tools → Page Analytics → Site analytics**, or the
  **Page Analytics tab** on a specific page. No setup needed — the built-in counter runs automatically.
- **…use Google Analytics (or another provider) instead?** Install the corresponding analytics module, then in
  **Page Analytics → Settings** choose that provider and paste its snippet / id. Its JS is injected into every
  page’s `<head>`, and (for GA) the built-in counter stops duplicating the work.
- **…read the visit data from my own code?** `MelisCmsPageAnalyticsService::getAnalytics($siteId)` returns the
  site’s analytics configuration; the raw hits live in `melis_cms_page_analytics` (see §B3).

---

# PART B — Technical reference

## B1. The two front-office hooks (how data is captured)

`MelisCmsPageAnalyticsListener` (attached in `src/Module.php`, extends
`MelisFrontSEODispatchRouterAbstractListener`) attaches **two** listeners on the front render pipeline:

| Event (verbatim) | Priority | What it does |
|---|---|---|
| `melisfront_site_dispatch_ready` | `-10000` | **only when `renderMode == 'front'`**: calls `MelisCmsDefaultPageAnalyticsService::saveAnalyticsData($pageId)` → records the deduped visit. |
| `melis_front_layout` | (default) | resolves the page’s site, reads its selected provider’s **JS snippet** (`pads_js_analytics`) and **injects it before `</head>`** (`str_replace('</head>', $script.'</head>', $content)`). |

## B2. The default provider — `MelisCmsDefaultPageAnalyticsService::saveAnalyticsData($pageId)`

The recording logic (fires `melis_cms_default_page_analytics_save_start` / `_end` events):

1. Resolve the **site** from the page (`MelisEngineTree::getSiteByPageId`).
2. **Google-Analytics takeover check:** if a `GoogleAnalyticsAPIService` is registered *and* returns a token +
   a `pads_settings->google_analytics_view_id`, **skip** the built-in counter (GA is handling it).
3. Otherwise, only for an **active published page** (`MelisEngineTablePagePublished`, `page_status` set):
   - take the session id (`$_COOKIE['PHPSESSID']` / `session_id()`), and
   - look up existing rows for `(pageId, session)` via
     `MelisCmsPageAnalyticsTable::getDataBySessionAndPageId()`; **insert a new hit only if there is none today**
     (`isToday($ph_date_visit)`), writing `ph_page_id`, `ph_session_id`, `ph_date_visit`, `ph_site_id`.

## B3. Data model (3 tables)

| Table | PK | Holds |
|---|---|---|
| `melis_cms_page_analytics` | `ph_id` | the **visit log** (one deduped hit): `ph_page_id`, `ph_session_id`, `ph_date_visit`, `ph_site_id`. *(Prefix `ph_` = “page hit”; note it differs from the table name.)* |
| `melis_cms_page_analytics_data` | `pad_id` | the per-site **provider selection**: `pad_site_id`, `pad_analytics_key` (which provider is active). |
| `melis_cms_page_analytics_data_settings` | `pads_id` | the per-(site, provider) **settings**: `pads_site_id`, `pads_analytics_key`, `pads_js_analytics` (the injected `<head>` snippet), `pads_settings` (e.g. `google_analytics_view_id`). |

DB model + SQL ship in `install/sql/` (installed via composer hooks; `MelisSetupController` handles setup).

## B4. Services, controllers & wiring

- **Services** (`config/module.config.php` aliases):
  - `MelisCmsPageAnalyticsService` — the main service implementing `getAnalytics($siteId)` (the provider
    contract); reads the site’s selection + settings.
  - `MelisCmsDefaultPageAnalyticsService` — the built-in recorder (§B2).
  - Table aliases: `MelisCmsPageAnalyticsTable`, `MelisCmsPageAnalyticsDataTable`,
    `MelisCmsPageAnalyticsDataSettingsTable`.
- **Controllers:**
  - `MelisCmsPageAnalyticsToolController` — the **site-wide tool** (container/header/content + the
    `tool-default-page-analytics-table` data table, the *analytics* tab and the *settings* tab actions).
  - `MelisCmsPageAnalyticsPageDetailsToolController` — the **per-page tab** (visits for one page).
  - `MelisSetupController` — self-install / post-download hooks.
- **BO placement** (`config/app.interface.php`):
  - left-menu tool under `melismarketing_toolstree_section → meliscms_site_tools_parent_menu` (**Site Tools**),
    tool `meliscms_page_analytics_tool_display`, with **Site analytics** + **Settings** tabs;
  - a CMS-page tab via `meliscms_page → meliscms_tabs → meliscms_page_analytics_tab` (name *Page Analytics*,
    icon *stats*) forwarding to the page-details controller.
- **Forms / factories** (`config/app.forms.php`, `src/Form/Factory/`): `PageAnalyticsSelect` (choose the
  provider) and `PageAnalyticsSiteSelect` (choose the site) populate the Settings form.
- **Other listener:** `MelisCmsPageAnalyticsFlashMessengerListener` — BO save/feedback messages.
- **Composer:** `melisplatform/melis-core` `^5.2` + `melisplatform/melis-cms` `^5.2` (README also expects
  melis-engine + melis-front — the front render pipeline the listeners hook into).

## B5. Quick code map

```
config/
  app.interface.php   ← Site-Tools tool + the per-page Page Analytics tab
  app.tools.php       ← the visit datatable (Id / Page id / Page name / Date visited)
  app.forms.php       ← the Settings form (provider + site select)
  module.config.php   ← routes, service & table aliases, controllers, form factories
src/
  Service/MelisCmsPageAnalyticsServiceInterface.php  ← the provider contract: getAnalytics($siteId)
  Service/MelisCmsPageAnalyticsService.php           ← main service (reads selection + settings)
  Service/MelisCmsDefaultPageAnalyticsService.php    ← built-in recorder (§B2)
  Listener/MelisCmsPageAnalyticsListener.php         ← the 2 front hooks (§B1)
  Listener/MelisCmsPageAnalyticsFlashMessengerListener.php
  Controller/MelisCmsPageAnalyticsToolController.php          ← site-wide tool
  Controller/MelisCmsPageAnalyticsPageDetailsToolController.php ← per-page tab
  Controller/MelisSetupController.php                ← self-install
  Model/Tables/…                                     ← the 3 tables (§B3)
install/sql/                                         ← DB model + SQL
```

---

*No screenshots are bundled with this doc. If you capture the Page Analytics tool (Site analytics / Settings
tabs) or the per-page analytics tab, save the PNGs under `./images/`, reference them 1:1 from Part A, and add a
Screenshot index here.*

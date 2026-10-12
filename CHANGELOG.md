# Changelog

## v6.0.5 - 2026-09-25
### Security
* **security:** declare the tool key on this module's legacy controllers (audit item 7.0)
### Fixed
* **analytics:** count visits only when an analytics module is selected (0011054)

## v6.0.4 - 2026-09-23
### Security
* **security:** declare the tool key on this module's controllers (audit item 7.0)
* **security:** parameterised SQL for date filters, ORDER BY and hand-quoted values (audit item 11.0)

## v6.0.3 - 2026-08-20
### Security
* Gate mutating/data actions in legacy tool controllers (CWE-862)
### Added
* **melisai:** add React screenshots to the MelisCmsPageAnalytics doc
* **analytics:** mount native GA site display in the Site analytics tool
* **page-analytics:** store the GA service-account key in DB instead of a file
* **site-analytics:** modular per-site analytics display + settings UX
### Fixed
* **page-analytics-react:** use the code-xml </> icon for the "New" toggle
### Docs
* **melisai:** React back-office AI documentation for MelisCmsPageAnalytics

## v6.0.1 - 2026-08-10
### Added
* **composer:** add docs link and authors block, swap zf2 keyword for laminas, bump php constraint to ^8.3|^8.5

## v6.0.0 - 2026-08-10
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
* Fix audit findings
* **rights:** put the Page Analytics rights key back on the left-menu path
### Added
* **page-analytics:** mobile-responsive React brick (useIsNarrow, no sm: classes)
* **page-analytics:** React site-analytics tool + settings panel + rebuild brick
* **marketplace:** add React back-office screenshots (etc/MarketPlace/images/react)
* **cms-react:** listes infinies keyset + tri server-side + icones de tri unifiees
* **cms-react:** pagination serveur + dates localisées (analytics/historique/versioning) + badges & filtre historique
* **cms-react:** droits/capabilities éditeur de page CMS + polish
* **react:** onglet Analytics natif pour l'editeur de page CMS (react-api)
* **page-analytics:** migration full-React (viewer natif + drill-down retire + toggle Old)
* **react:** page-analytics React brick (iframe)
* **MelisAI:** add MelisCmsPageAnalytics AI & developer guide
### Fixed
* **mobile:** touch-compatible column drag-and-drop, KPI icons, translate New/Old toggle
* **page-analytics-react:** panneaux colonnes/export scrollables (évite le débordement)
* Update analytics tool (controller, views, CSS, interface config)
### Changed
* Update MelisCmsDefaultPageAnalyticsService.php
### Dependencies & build
* **composer:** bump melis-core/melis-cms constraint to ^6.0

## v5.3.0 - 2024-09-25
### Fixed
* Fix php error undefined property $google_analytics_view_id
### Changed
* Page analytics in page edition
* Edit related to google analytics
* Update jQuery 3.7.1 migration

## v5.2.0 - 2024-06-06
* Maintenance release.

## v5.1.0 - 2024-02-13
### Added
* Add propertyId in google analytics

## v5.0.0 - 2022-06-22
### Changed
* Changed deprecated ArraySerializable to ArraySerializableHydrator and updated other functions affected by php 8

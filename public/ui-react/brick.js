(function(react, react_jsx_runtime) {
	//#region src/page-analytics-api.ts
	var XHR_HEADER = { "X-Requested-With": "XMLHttpRequest" };
	async function apiGet(url) {
		const res = await fetch(url, {
			headers: { ...XHR_HEADER },
			credentials: "include"
		});
		const json = await res.json();
		if (!json.success) throw new Error(json.error || `HTTP ${res.status}`);
		return json.data;
	}
	function qs(params) {
		const sp = new URLSearchParams();
		for (const [k, v] of Object.entries(params)) if (v !== void 0 && v !== null && v !== "" && v !== 0) sp.set(k, String(v));
		const s = sp.toString();
		return s ? `?${s}` : "";
	}
	function fetchAnalytics(p = {}) {
		return apiGet(`/melis/react-api/page-analytics${qs({
			limit: p.limit,
			search: p.search,
			site: p.site,
			sort: p.sort,
			dir: p.dir,
			after: p.after
		})}`);
	}
	function fetchAnalyticsStats(p = {}) {
		return apiGet(`/melis/react-api/page-analytics/stats${qs({
			search: p.search,
			site: p.site
		})}`);
	}
	function fetchAnalyticsSites() {
		return apiGet("/melis/react-api/page-analytics/sites");
	}
	function fetchAnalyticsSettings(siteId, key) {
		return apiGet(`/melis/react-api/page-analytics/settings${qs({
			site: siteId,
			key
		})}`);
	}
	var LEGACY_SAVE_URL = "/melis/MelisCmsPageAnalytics/MelisCmsPageAnalyticsTool/save";
	async function saveAnalyticsSettings(form) {
		return await (await fetch(LEGACY_SAVE_URL, {
			method: "POST",
			headers: { ...XHR_HEADER },
			credentials: "include",
			body: form
		})).json();
	}
	//#endregion
	//#region src/use-keyset-list.ts
	function useKeysetList(opts) {
		const LIMIT = opts.limit ?? 25;
		const [items, setItems] = (0, react.useState)(opts.initial?.items ?? []);
		const [total, setTotal] = (0, react.useState)(opts.initial?.total ?? 0);
		const [loading, setLoading] = (0, react.useState)(false);
		const [hasMore, setHasMore] = (0, react.useState)(opts.initial?.hasMore ?? false);
		const [sortCol, setSortCol] = (0, react.useState)(opts.initial?.sortCol ?? opts.defaultSort ?? "id");
		const [sortDir, setSortDir] = (0, react.useState)(opts.initial?.sortDir ?? opts.defaultDir ?? "desc");
		const cursorRef = (0, react.useRef)(opts.initial?.cursor ?? null);
		const loadingRef = (0, react.useRef)(false);
		const reqIdRef = (0, react.useRef)(0);
		const sentinelRef = (0, react.useRef)(null);
		const fetcherRef = (0, react.useRef)(opts.fetcher);
		fetcherRef.current = opts.fetcher;
		const runLoad = (0, react.useCallback)(async (reset) => {
			if (!reset && loadingRef.current) return;
			const myReq = ++reqIdRef.current;
			loadingRef.current = true;
			setLoading(true);
			const after = reset ? void 0 : cursorRef.current ?? void 0;
			try {
				const res = await fetcherRef.current({
					limit: LIMIT,
					sort: sortCol,
					dir: sortDir,
					after
				});
				if (myReq !== reqIdRef.current) return;
				cursorRef.current = res.nextCursor;
				setHasMore(res.nextCursor !== null);
				setTotal(res.total);
				setItems((prev) => reset ? res.items : [...prev, ...res.items]);
			} catch {} finally {
				if (myReq === reqIdRef.current) {
					setLoading(false);
					loadingRef.current = false;
				}
			}
		}, [
			sortCol,
			sortDir,
			LIMIT
		]);
		const didInitRef = (0, react.useRef)(false);
		(0, react.useEffect)(() => {
			if (!didInitRef.current) {
				didInitRef.current = true;
				if (opts.skipInitial) return;
			}
			runLoad(true);
		}, [
			...opts.deps,
			sortCol,
			sortDir
		]);
		(0, react.useEffect)(() => {
			if (!sentinelRef.current || !hasMore) return;
			const obs = new IntersectionObserver(([entry]) => {
				if (entry.isIntersecting) runLoad(false);
			}, { rootMargin: "120px" });
			obs.observe(sentinelRef.current);
			return () => obs.disconnect();
		}, [hasMore, runLoad]);
		const toggleSort = (0, react.useCallback)((id) => {
			setSortCol((cur) => {
				if (cur === id) {
					setSortDir((d) => d === "asc" ? "desc" : "asc");
					return cur;
				}
				setSortDir(id === "id" ? "desc" : "asc");
				return id;
			});
		}, []);
		/** Force un rechargement depuis le début (refresh / reset filtres). */
		const reload = (0, react.useCallback)(() => {
			cursorRef.current = null;
			runLoad(true);
		}, [runLoad]);
		/** Retire un élément localement (après delete) sans recharger. */
		const removeLocal = (0, react.useCallback)((pred) => {
			setItems((prev) => prev.filter((it) => !pred(it)));
			setTotal((t) => Math.max(0, t - 1));
		}, []);
		/** Snapshot pour le cache module-level. */
		const snapshot = () => ({
			items,
			total,
			cursor: cursorRef.current,
			hasMore,
			sortCol,
			sortDir
		});
		return {
			items,
			setItems,
			total,
			loading,
			hasMore,
			sentinelRef,
			sortCol,
			sortDir,
			setSortCol,
			setSortDir,
			toggleSort,
			reload,
			removeLocal,
			snapshot
		};
	}
	//#endregion
	//#region src/ui.tsx
	function currentLang$1() {
		return (document.documentElement.lang || "en").toLowerCase().startsWith("fr") ? "fr" : "en";
	}
	var DICT$1 = {
		fr: {
			title: "Site analytics",
			subtitle: "Visites par page (suivi intégré Melis)",
			old_hint: "Outil Melis classique — onglets Analytics et Paramètres (affectation aux sites)",
			site: "Site",
			site_all: "Tous les sites",
			search: "Rechercher une page…",
			empty: "Aucune donnée de visite",
			count: "{n} pages — fin de la liste",
			kpi_hits: "Visites",
			kpi_pages: "Pages suivies",
			kpi_sites: "Sites",
			kpi_last: "Dernière visite",
			col_pageId: "ID page",
			col_pageName: "Page",
			col_count: "Visites",
			col_last: "Dernière visite",
			columns: "Colonnes",
			export: "Exporter",
			cols_visible: "Visibles",
			cols_hidden: "Masquées",
			drag_here: "Glisser ici",
			reset: "Réinitialiser",
			details: "Voir les visites",
			back: "Retour",
			refresh: "Rafraîchir",
			loading: "Chargement…",
			none: "—",
			deleted: "(page supprimée)",
			no_access: "Vous n’avez pas les droits pour consulter cet outil.",
			detail_title: "Visites de « {n} »",
			detail_subtitle: "Détail des visites individuelles",
			d_col_id: "ID",
			d_col_date: "Date de visite",
			d_col_session: "Session",
			d_search: "Rechercher une visite…",
			detail_empty: "Aucune visite",
			detail_count: "{n} visites — fin de la liste",
			tab_analytics: "Analytics",
			tab_settings: "Paramètres",
			set_subtitle: "Affecter un module analytics à un site et régler ses paramètres",
			set_site: "Site",
			set_site_ph: "Sélectionner un site",
			set_module: "Module analytics",
			set_module_ph: "Sélectionner un module analytics",
			set_module_help: "Le module choisi fournit l’affichage de l’onglet Analytics pour ce site.",
			set_js: "Script analytics personnalisé",
			set_js_help: "JavaScript injecté dans le <head> de toutes les pages du front de ce site.",
			set_js_admin: "Seul un administrateur de la plateforme peut modifier ce script.",
			set_save: "Enregistrer",
			set_saving: "Enregistrement…",
			set_pick_site: "Sélectionnez un site pour afficher ses paramètres.",
			set_no_settings: "Ce module n’a pas de paramètre supplémentaire.",
			set_file_legacy: "L’envoi de fichier se fait ici comme dans l’outil classique.",
			set_current_file: "Fichier actuel : {n}",
			set_error: "Échec de l’enregistrement.",
			set_check_fields: "Veuillez vérifier les champs obligatoires."
		},
		en: {
			title: "Site analytics",
			subtitle: "Visits per page (Melis built-in tracking)",
			old_hint: "Classic Melis tool — Analytics and Settings tabs (assign to sites)",
			site: "Site",
			site_all: "All sites",
			search: "Search a page…",
			empty: "No visit data",
			count: "{n} pages — end of list",
			kpi_hits: "Visits",
			kpi_pages: "Tracked pages",
			kpi_sites: "Sites",
			kpi_last: "Last visit",
			col_pageId: "Page ID",
			col_pageName: "Page",
			col_count: "Visits",
			col_last: "Last visit",
			columns: "Columns",
			export: "Export",
			cols_visible: "Visible",
			cols_hidden: "Hidden",
			drag_here: "Drag here",
			reset: "Reset",
			details: "View visits",
			back: "Back",
			refresh: "Refresh",
			loading: "Loading…",
			none: "—",
			deleted: "(deleted page)",
			no_access: "You do not have permission to view this tool.",
			detail_title: "Visits of “{n}”",
			detail_subtitle: "Individual visit details",
			d_col_id: "ID",
			d_col_date: "Visit date",
			d_col_session: "Session",
			d_search: "Search a visit…",
			detail_empty: "No visit",
			detail_count: "{n} visits — end of list",
			tab_analytics: "Analytics",
			tab_settings: "Settings",
			set_subtitle: "Assign an analytics module to a site and configure its parameters",
			set_site: "Site",
			set_site_ph: "Select a site",
			set_module: "Analytics module",
			set_module_ph: "Select an analytics module",
			set_module_help: "The selected module provides the Analytics tab display for this site.",
			set_js: "Custom analytics script",
			set_js_help: "JavaScript injected into the <head> of every front page of this site.",
			set_js_admin: "Only a platform administrator can change this script.",
			set_save: "Save",
			set_saving: "Saving…",
			set_pick_site: "Select a site to display its settings.",
			set_no_settings: "This module has no additional parameter.",
			set_file_legacy: "File upload works here just like in the classic tool.",
			set_current_file: "Current file: {n}",
			set_error: "Save failed.",
			set_check_fields: "Please check the required fields."
		}
	};
	function useT() {
		const lang = currentLang$1();
		return (key, vars) => {
			let s = DICT$1[lang][key] ?? key;
			if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v));
			return s;
		};
	}
	function fmtDate(value, lang) {
		if (!value) return "—";
		const d = new Date(String(value).replace(" ", "T"));
		if (isNaN(d.getTime())) return String(value);
		return d.toLocaleString(lang === "fr" ? "fr-FR" : "en-GB", {
			year: "numeric",
			month: "2-digit",
			day: "2-digit",
			hour: "2-digit",
			minute: "2-digit"
		});
	}
	var card$1 = {
		border: "1px solid var(--color-border)",
		background: "var(--color-card)",
		borderRadius: 12,
		boxShadow: "0 1px 2px rgba(0,0,0,.04)"
	};
	var inputCss = {
		height: 40,
		width: "100%",
		boxSizing: "border-box",
		borderRadius: 8,
		border: "1px solid var(--color-input,var(--color-border))",
		background: "var(--color-card)",
		color: "var(--color-foreground)",
		padding: "0 12px",
		fontSize: 14,
		outline: "none"
	};
	var btnPrimary$1 = {
		display: "inline-flex",
		alignItems: "center",
		gap: 6,
		height: 36,
		padding: "0 14px",
		borderRadius: 8,
		border: 0,
		background: "var(--color-primary)",
		color: "var(--color-primary-foreground,#fff)",
		fontSize: 14,
		fontWeight: 500,
		cursor: "pointer"
	};
	var btnGhost$1 = {
		display: "inline-flex",
		alignItems: "center",
		gap: 6,
		height: 36,
		padding: "0 12px",
		borderRadius: 8,
		border: "1px solid var(--color-border)",
		background: "var(--color-card)",
		color: "var(--color-foreground)",
		fontSize: 14,
		cursor: "pointer"
	};
	var iconBtn = {
		display: "inline-flex",
		alignItems: "center",
		justifyContent: "center",
		width: 28,
		height: 28,
		borderRadius: 6,
		border: 0,
		background: "transparent",
		color: "var(--color-muted-foreground)",
		cursor: "pointer"
	};
	var th = {
		textAlign: "left",
		padding: "10px 16px",
		fontSize: 11,
		fontWeight: 600,
		textTransform: "uppercase",
		letterSpacing: ".04em",
		color: "var(--color-muted-foreground)",
		whiteSpace: "nowrap"
	};
	var td = {
		padding: "10px 16px",
		fontSize: 14,
		color: "var(--color-foreground)",
		borderTop: "1px solid var(--color-border)"
	};
	var GripIcon$1 = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: {
			width: 13,
			height: 13,
			flexShrink: 0,
			color: "var(--color-muted-foreground)"
		},
		viewBox: "0 0 24 24",
		fill: "currentColor",
		children: [
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "6",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "6",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "12",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "12",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "18",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "18",
				r: "1.5"
			})
		]
	});
	function Kpi({ label: lbl, value, narrow = false }) {
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				...card$1,
				display: "flex",
				flexDirection: "column",
				gap: 2,
				padding: narrow ? 12 : 16,
				...narrow ? {
					flex: "1 1 calc(50% - 6px)",
					minWidth: 0
				} : {
					flex: 1,
					minWidth: 140
				}
			},
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
				style: {
					fontSize: 12,
					color: "var(--color-muted-foreground)"
				},
				children: lbl
			}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
				style: {
					fontSize: narrow ? 16 : 22,
					fontWeight: 700,
					minWidth: 0,
					overflowWrap: "break-word"
				},
				children: value == null ? "…" : value
			})]
		});
	}
	var visibleCols = (c) => c.filter((x) => x.visible);
	function makeColStore(key, defaults) {
		const load = () => {
			try {
				const raw = localStorage.getItem(key);
				if (!raw) return defaults;
				const saved = JSON.parse(raw);
				const ordered = saved.map((s) => {
					const d = defaults.find((c) => c.id === s.id);
					return d ? {
						id: d.id,
						visible: s.visible
					} : null;
				}).filter(Boolean);
				const missing = defaults.filter((d) => !saved.find((s) => s.id === d.id));
				return [...ordered, ...missing];
			} catch {
				return defaults;
			}
		};
		const save = (c) => {
			try {
				localStorage.setItem(key, JSON.stringify(c));
			} catch {}
		};
		return {
			load,
			save,
			defaults
		};
	}
	var panelCss$1 = {
		display: "flex",
		flexDirection: "column",
		gap: 2,
		minHeight: 130,
		maxHeight: "min(48vh, 320px)",
		overflowY: "auto",
		minWidth: 0,
		borderRadius: 8,
		border: "1px dashed var(--color-border)",
		padding: 6
	};
	var panelTitle$1 = {
		padding: "0 6px 4px",
		fontSize: 10,
		fontWeight: 600,
		textTransform: "uppercase",
		letterSpacing: ".06em",
		color: "var(--color-muted-foreground)"
	};
	function ColManager({ anchorRef, cols, labelFor, onChange, onSave, defaults, onClose }) {
		const t = useT();
		const [dragId, setDragId] = (0, react.useState)(null);
		const [over, setOver] = (0, react.useState)(null);
		const [pos, setPos] = (0, react.useState)(null);
		const shown = cols.filter((c) => c.visible);
		const hidden = cols.filter((c) => !c.visible);
		(0, react.useLayoutEffect)(() => {
			const anchor = anchorRef.current;
			if (!anchor) return;
			const rect = anchor.getBoundingClientRect();
			const margin = 8;
			const spaceBelow = window.innerHeight - rect.bottom - margin;
			const spaceAbove = rect.top - margin;
			const width = Math.min(380, window.innerWidth - margin * 2);
			const left = Math.min(Math.max(margin, rect.right - width), window.innerWidth - width - margin);
			if (spaceBelow >= 200 || spaceBelow >= spaceAbove) setPos({
				top: rect.bottom + 6,
				left,
				width,
				maxHeight: Math.max(160, spaceBelow - 6)
			});
			else setPos({
				bottom: window.innerHeight - rect.top + 6,
				left,
				width,
				maxHeight: Math.max(160, spaceAbove - 6)
			});
		}, [anchorRef]);
		function drop(panel) {
			if (!dragId) return;
			const upd = {
				...cols.find((c) => c.id === dragId),
				visible: panel === "visible"
			};
			let vList = shown.filter((c) => c.id !== dragId);
			const hList = hidden.filter((c) => c.id !== dragId);
			if (panel === "visible") {
				const dst = over?.id;
				if (!dst || dst === "__panel__") vList = [...vList, upd];
				else {
					const i = vList.findIndex((c) => c.id === dst);
					vList = i === -1 ? [...vList, upd] : [
						...vList.slice(0, i),
						upd,
						...vList.slice(i)
					];
				}
				const next = [...vList, ...hList];
				onChange(next);
				onSave(next);
			} else {
				const next = [
					...vList,
					...hList,
					upd
				];
				onChange(next);
				onSave(next);
			}
			setDragId(null);
			setOver(null);
		}
		function item(col, panel) {
			const isOver = over?.id === col.id && over?.panel === panel;
			return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				draggable: true,
				onDragStart: () => setDragId(col.id),
				onDragEnd: () => {
					setDragId(null);
					setOver(null);
				},
				onDragOver: (e) => {
					e.preventDefault();
					e.stopPropagation();
					if (over?.id !== col.id || over?.panel !== panel) setOver({
						id: col.id,
						panel
					});
				},
				onDrop: (e) => {
					e.preventDefault();
					drop(panel);
				},
				style: {
					display: "flex",
					alignItems: "center",
					gap: 8,
					borderRadius: 8,
					padding: "6px 8px",
					fontSize: 14,
					cursor: "grab",
					userSelect: "none",
					opacity: dragId === col.id ? .4 : 1,
					background: isOver ? "color-mix(in srgb, var(--color-primary) 12%, transparent)" : "transparent",
					boxShadow: isOver ? "0 0 0 1px color-mix(in srgb, var(--color-primary) 35%, transparent)" : "none"
				},
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(GripIcon$1, {}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
					style: {
						flex: 1,
						overflow: "hidden",
						textOverflow: "ellipsis",
						whiteSpace: "nowrap"
					},
					children: labelFor(col.id)
				})]
			}, col.id);
		}
		if (!pos) return null;
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				...card$1,
				position: "fixed",
				left: pos.left,
				width: pos.width,
				zIndex: 50,
				maxHeight: pos.maxHeight,
				overflowY: "auto",
				display: "flex",
				flexDirection: "column",
				...pos.top != null ? { top: pos.top } : { bottom: pos.bottom }
			},
			children: [
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "flex",
						alignItems: "center",
						justifyContent: "space-between",
						padding: "10px 12px",
						borderBottom: "1px solid var(--color-border)"
					},
					children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
						style: {
							fontSize: 14,
							fontWeight: 600
						},
						children: t("columns")
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
						style: {
							...iconBtn,
							width: 22,
							height: 22
						},
						onClick: onClose,
						children: "✕"
					})]
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "grid",
						gridTemplateColumns: "1fr 1fr",
						gap: 8,
						padding: 12
					},
					children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: panelCss$1,
						onDragOver: (e) => {
							e.preventDefault();
							if (over?.id !== "__panel__" || over?.panel !== "hidden") setOver({
								id: "__panel__",
								panel: "hidden"
							});
						},
						onDrop: (e) => {
							e.preventDefault();
							drop("hidden");
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
							style: panelTitle$1,
							children: t("cols_hidden")
						}), hidden.length === 0 ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								flex: 1,
								display: "flex",
								alignItems: "center",
								justifyContent: "center",
								fontSize: 11,
								color: "var(--color-muted-foreground)",
								opacity: .5,
								padding: "16px 0"
							},
							children: t("drag_here")
						}) : hidden.map((c) => item(c, "hidden"))]
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: panelCss$1,
						onDragOver: (e) => {
							e.preventDefault();
							if (over?.id !== "__panel__" || over?.panel !== "visible") setOver({
								id: "__panel__",
								panel: "visible"
							});
						},
						onDrop: (e) => {
							e.preventDefault();
							drop("visible");
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
							style: panelTitle$1,
							children: t("cols_visible")
						}), shown.length === 0 ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								flex: 1,
								display: "flex",
								alignItems: "center",
								justifyContent: "center",
								fontSize: 11,
								color: "var(--color-muted-foreground)",
								opacity: .5,
								padding: "16px 0"
							},
							children: t("drag_here")
						}) : shown.map((c) => item(c, "visible"))]
					})]
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
					style: {
						borderTop: "1px solid var(--color-border)",
						padding: 6
					},
					children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
						style: {
							...btnGhost$1,
							width: "100%",
							height: 30,
							border: 0,
							justifyContent: "center",
							color: "var(--color-muted-foreground)"
						},
						onClick: () => {
							onChange(defaults);
							onSave(defaults);
						},
						children: t("reset")
					})
				})
			]
		});
	}
	//#endregion
	//#region src/shared/useIsNarrow.ts
	/**
	* True when the viewport is narrower than `breakpoint`. Drives every responsive decision on
	* this brick as a JS ternary (inline styles) instead of a CSS media query — see the
	* `melis-react-mobile-responsive` skill for why.
	*/
	function useIsNarrow(breakpoint = 640) {
		const [narrow, setNarrow] = (0, react.useState)(() => window.innerWidth < breakpoint);
		(0, react.useEffect)(() => {
			const onResize = () => setNarrow(window.innerWidth < breakpoint);
			window.addEventListener("resize", onResize);
			return () => window.removeEventListener("resize", onResize);
		}, [breakpoint]);
		return narrow;
	}
	//#endregion
	//#region src/ExportModal.tsx
	function getXLSX() {
		return window.MelisXLSX ?? null;
	}
	function currentLang() {
		return (document.documentElement.lang || "en").toLowerCase().startsWith("fr") ? "fr" : "en";
	}
	var DICT = {
		fr: {
			export: "Exporter",
			title: "Exporter les données",
			subtitle: "{n} lignes avec les filtres actifs",
			included: "Incluses",
			excluded: "Exclues",
			drag_here: "Glisser ici",
			download: "Télécharger {fmt}",
			exporting: "Export…",
			error: "Erreur lors de l’export",
			cancel: "Annuler"
		},
		en: {
			export: "Export",
			title: "Export data",
			subtitle: "{n} rows with the active filters",
			included: "Included",
			excluded: "Excluded",
			drag_here: "Drag here",
			download: "Download {fmt}",
			exporting: "Exporting…",
			error: "Error during export",
			cancel: "Cancel"
		}
	};
	function tr(key, vars) {
		let s = DICT[currentLang()][key] ?? key;
		if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v));
		return s;
	}
	var card = {
		border: "1px solid var(--color-border)",
		background: "var(--color-card)",
		borderRadius: 12,
		boxShadow: "0 1px 2px rgba(0,0,0,.04)"
	};
	var panelCss = {
		display: "flex",
		flexDirection: "column",
		gap: 2,
		minHeight: 100,
		maxHeight: "min(48vh, 320px)",
		overflowY: "auto",
		minWidth: 0,
		borderRadius: 8,
		border: "1px dashed var(--color-border)",
		padding: 6
	};
	var panelTitle = {
		padding: "0 6px 4px",
		fontSize: 10,
		fontWeight: 600,
		textTransform: "uppercase",
		letterSpacing: ".06em",
		color: "var(--color-muted-foreground)"
	};
	var btnGhost = {
		display: "inline-flex",
		alignItems: "center",
		gap: 6,
		height: 34,
		padding: "0 12px",
		borderRadius: 8,
		border: "1px solid var(--color-border)",
		background: "var(--color-card)",
		color: "var(--color-foreground)",
		fontSize: 14,
		cursor: "pointer"
	};
	var btnPrimary = {
		display: "inline-flex",
		alignItems: "center",
		gap: 6,
		height: 34,
		padding: "0 14px",
		borderRadius: 8,
		border: 0,
		background: "var(--color-primary)",
		color: "var(--color-primary-foreground,#fff)",
		fontSize: 14,
		fontWeight: 500,
		cursor: "pointer"
	};
	var GripIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: {
			width: 13,
			height: 13,
			flexShrink: 0,
			color: "var(--color-muted-foreground)"
		},
		viewBox: "0 0 24 24",
		fill: "currentColor",
		children: [
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "6",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "6",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "12",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "12",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "9",
				cy: "18",
				r: "1.5"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("circle", {
				cx: "15",
				cy: "18",
				r: "1.5"
			})
		]
	});
	var DownloadIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("svg", {
		style: {
			width: 15,
			height: 15,
			flexShrink: 0
		},
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" })
	});
	var ExcelIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: {
			width: 16,
			height: 16,
			flexShrink: 0
		},
		viewBox: "0 0 24 24",
		fill: "none",
		children: [
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("rect", {
				x: "1",
				y: "1",
				width: "22",
				height: "22",
				rx: "3",
				fill: "#217346"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("line", {
				x1: "7.5",
				y1: "7.5",
				x2: "16.5",
				y2: "16.5",
				stroke: "white",
				strokeWidth: "2.5",
				strokeLinecap: "round"
			}),
			/* @__PURE__ */ (0, react_jsx_runtime.jsx)("line", {
				x1: "16.5",
				y1: "7.5",
				x2: "7.5",
				y2: "16.5",
				stroke: "white",
				strokeWidth: "2.5",
				strokeLinecap: "round"
			})
		]
	});
	var CsvIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: {
			width: 16,
			height: 16,
			flexShrink: 0
		},
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" }), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M14 2v6h6M16 13H8M16 17H8M10 9H8" })]
	});
	function ExportModal({ cols, labelFor, fetchAll, getCell, filename, sheetName, total, onClose }) {
		const xlsx = getXLSX();
		const narrow = useIsNarrow();
		const [included, setIncluded] = (0, react.useState)(() => cols.filter((c) => c.visible));
		const [excluded, setExcluded] = (0, react.useState)(() => cols.filter((c) => !c.visible));
		const [format, setFormat] = (0, react.useState)(xlsx ? "xlsx" : "csv");
		const [exporting, setExporting] = (0, react.useState)(false);
		const [dragId, setDragId] = (0, react.useState)(null);
		const [over, setOver] = (0, react.useState)(null);
		function drop(panel) {
			if (!dragId) return;
			const src = [...included, ...excluded].find((c) => c.id === dragId);
			let inc = included.filter((c) => c.id !== dragId);
			let exc = excluded.filter((c) => c.id !== dragId);
			if (panel === "included") {
				const dst = over?.id;
				if (!dst || dst === "__panel__") inc = [...inc, src];
				else {
					const i = inc.findIndex((c) => c.id === dst);
					inc = i === -1 ? [...inc, src] : [
						...inc.slice(0, i),
						src,
						...inc.slice(i)
					];
				}
			} else exc = [...exc, src];
			setIncluded(inc);
			setExcluded(exc);
			setDragId(null);
			setOver(null);
		}
		function item(col, panel) {
			const isOver = over?.id === col.id && over?.panel === panel;
			return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				draggable: true,
				onDragStart: () => setDragId(col.id),
				onDragEnd: () => {
					setDragId(null);
					setOver(null);
				},
				onDragOver: (e) => {
					e.preventDefault();
					e.stopPropagation();
					if (over?.id !== col.id || over?.panel !== panel) setOver({
						id: col.id,
						panel
					});
				},
				onDrop: (e) => {
					e.preventDefault();
					drop(panel);
				},
				style: {
					display: "flex",
					alignItems: "center",
					gap: 8,
					borderRadius: 8,
					padding: "6px 8px",
					fontSize: 14,
					cursor: "grab",
					userSelect: "none",
					opacity: dragId === col.id ? .4 : 1,
					background: isOver ? "color-mix(in srgb, var(--color-primary) 12%, transparent)" : "transparent",
					boxShadow: isOver ? "0 0 0 1px color-mix(in srgb, var(--color-primary) 35%, transparent)" : "none"
				},
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(GripIcon, {}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
					style: {
						flex: 1,
						overflow: "hidden",
						textOverflow: "ellipsis",
						whiteSpace: "nowrap"
					},
					children: labelFor(col.id)
				})]
			}, col.id);
		}
		const ph = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
			style: {
				flex: 1,
				display: "flex",
				alignItems: "center",
				justifyContent: "center",
				fontSize: 11,
				color: "var(--color-muted-foreground)",
				opacity: .5,
				padding: "12px 0"
			},
			children: tr("drag_here")
		});
		async function doExport() {
			if (included.length === 0) return;
			setExporting(true);
			try {
				const all = await fetchAll();
				const header = included.map((c) => labelFor(c.id));
				const rows = all.map((it) => included.map((c) => getCell(it, c.id)));
				const dateStr = (/* @__PURE__ */ new Date()).toISOString().slice(0, 10);
				if (format === "xlsx" && xlsx) {
					const ws = xlsx.utils.aoa_to_sheet([header, ...rows]);
					const wb = xlsx.utils.book_new();
					xlsx.utils.book_append_sheet(wb, ws, sheetName);
					xlsx.writeFile(wb, `${filename}-${dateStr}.xlsx`);
				} else {
					const csv = [header, ...rows].map((r) => r.map((v) => `"${String(v ?? "").replace(/"/g, "\"\"")}"`).join(",")).join("\n");
					const blob = new Blob(["﻿" + csv], { type: "text/csv;charset=utf-8;" });
					const url = URL.createObjectURL(blob);
					const a = Object.assign(document.createElement("a"), {
						href: url,
						download: `${filename}-${dateStr}.csv`
					});
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					URL.revokeObjectURL(url);
				}
				onClose();
			} catch (e) {
				alert(e instanceof Error ? e.message : tr("error"));
			} finally {
				setExporting(false);
			}
		}
		const tab = (active) => ({
			flex: 1,
			display: "inline-flex",
			alignItems: "center",
			justifyContent: "center",
			gap: 8,
			height: 36,
			borderRadius: 6,
			border: 0,
			fontSize: 14,
			fontWeight: 500,
			cursor: "pointer",
			background: active ? "var(--color-card)" : "transparent",
			color: active ? "var(--color-foreground)" : "var(--color-muted-foreground)",
			boxShadow: active ? "0 1px 2px rgba(0,0,0,.06)" : "none"
		});
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
			style: {
				position: "fixed",
				inset: 0,
				zIndex: 60,
				display: "flex",
				alignItems: "center",
				justifyContent: "center",
				background: "rgba(0,0,0,.5)",
				...narrow ? { padding: 12 } : {}
			},
			onClick: (e) => {
				if (e.target === e.currentTarget) onClose();
			},
			children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					...card,
					width: "100%",
					maxWidth: 480,
					...narrow ? {
						maxHeight: "calc(100vh - 24px)",
						overflowY: "auto",
						boxSizing: "border-box"
					} : {}
				},
				children: [
					/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: {
							display: "flex",
							alignItems: "flex-start",
							justifyContent: "space-between",
							padding: "16px 20px",
							borderBottom: "1px solid var(--color-border)"
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("h2", {
							style: {
								fontSize: 14,
								fontWeight: 600,
								margin: 0
							},
							children: tr("title")
						}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
							style: {
								fontSize: 12,
								color: "var(--color-muted-foreground)",
								margin: "2px 0 0"
							},
							children: tr("subtitle", { n: total })
						})] }), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
							style: {
								border: 0,
								background: "transparent",
								cursor: "pointer",
								color: "var(--color-muted-foreground)",
								fontSize: 16
							},
							onClick: onClose,
							children: "✕"
						})]
					}),
					/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: {
							padding: 16,
							display: "flex",
							flexDirection: "column",
							gap: 16
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
							style: {
								display: "flex",
								gap: 4,
								padding: 4,
								borderRadius: 8,
								border: "1px solid var(--color-border)",
								background: "color-mix(in srgb, var(--color-muted,#888) 12%, transparent)"
							},
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
								style: tab(format === "xlsx"),
								disabled: !xlsx,
								onClick: () => xlsx && setFormat("xlsx"),
								title: xlsx ? "" : "XLSX indisponible",
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(ExcelIcon, {}), "Excel (.xlsx)"]
							}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
								style: tab(format === "csv"),
								onClick: () => setFormat("csv"),
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(CsvIcon, {}), "CSV (.csv)"]
							})]
						}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
							style: {
								display: "grid",
								gridTemplateColumns: narrow ? "minmax(0, 1fr)" : "1fr 1fr",
								gap: 8
							},
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
								style: panelCss,
								onDragOver: (e) => {
									e.preventDefault();
									if (over?.id !== "__panel__" || over?.panel !== "excluded") setOver({
										id: "__panel__",
										panel: "excluded"
									});
								},
								onDrop: (e) => {
									e.preventDefault();
									drop("excluded");
								},
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
									style: panelTitle,
									children: tr("excluded")
								}), excluded.length === 0 ? ph() : excluded.map((c) => item(c, "excluded"))]
							}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
								style: panelCss,
								onDragOver: (e) => {
									e.preventDefault();
									if (over?.id !== "__panel__" || over?.panel !== "included") setOver({
										id: "__panel__",
										panel: "included"
									});
								},
								onDrop: (e) => {
									e.preventDefault();
									drop("included");
								},
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
									style: panelTitle,
									children: tr("included")
								}), included.length === 0 ? ph() : included.map((c) => item(c, "included"))]
							})]
						})]
					}),
					/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: {
							display: "flex",
							justifyContent: "flex-end",
							gap: 8,
							padding: "12px 16px",
							borderTop: "1px solid var(--color-border)"
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
							style: btnGhost,
							onClick: onClose,
							disabled: exporting,
							children: tr("cancel")
						}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
							style: {
								...btnPrimary,
								opacity: included.length === 0 || exporting ? .6 : 1
							},
							onClick: doExport,
							disabled: exporting || included.length === 0,
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(DownloadIcon, {}), exporting ? tr("exporting") : tr("download", { fmt: format.toUpperCase() })]
						})]
					})
				]
			})
		});
	}
	//#endregion
	//#region src/ViewToggle.tsx
	var sIcon$1 = {
		width: 15,
		height: 15,
		flexShrink: 0
	};
	var SparkIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("svg", {
		style: sIcon$1,
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z" })
	});
	var LayoutIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: sIcon$1,
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("rect", {
			x: "3",
			y: "3",
			width: "18",
			height: "18",
			rx: "2"
		}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M3 9h18M9 21V9" })]
	});
	/** `compact` (opt-in, défaut `false`) : icônes seules — pour les viewports étroits. Le toggle
	*  n'est JAMAIS masqué, il est seulement resserré (cf. skill melis-react-mobile-responsive). */
	function ViewToggle({ mode, onChange, compact = false }) {
		const tab = (active) => ({
			display: "inline-flex",
			alignItems: "center",
			gap: 6,
			height: 30,
			padding: compact ? "0 8px" : "0 12px",
			borderRadius: 6,
			border: 0,
			fontSize: 12,
			fontWeight: 500,
			cursor: "pointer",
			background: active ? "var(--color-card)" : "transparent",
			color: active ? "var(--color-foreground)" : "var(--color-muted-foreground)",
			boxShadow: active ? "0 1px 2px rgba(0,0,0,.06)" : "none"
		});
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "inline-flex",
				gap: 4,
				padding: 4,
				borderRadius: 8,
				border: "1px solid var(--color-border)",
				background: "color-mix(in srgb, var(--color-muted,#888) 12%, transparent)"
			},
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
				style: tab(mode === "react"),
				onClick: () => onChange("react"),
				title: compact ? "New" : void 0,
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(SparkIcon, {}), !compact && "New"]
			}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
				style: tab(mode === "iframe"),
				onClick: () => onChange("iframe"),
				title: compact ? "Old" : void 0,
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(LayoutIcon, {}), !compact && "Old"]
			})]
		});
	}
	//#endregion
	//#region src/shared/melis-form-errors.tsx
	function postNotif(kind, title, message, issues) {
		try {
			const fields = (issues ?? []).filter((i) => i && i.label).map((i) => ({
				label: i.label,
				messages: [i.message]
			}));
			window.postMessage({
				__melisNotif: true,
				kind,
				title,
				message,
				fields
			}, "*");
		} catch {}
	}
	function okNotify(title, message = "") {
		postNotif("ok", title, message);
	}
	/** Error toast. Pass `issues` to list offending fields inside the toast (host renders them). */
	function koNotify(title, message = "", issues) {
		postNotif("ko", title, message, issues);
	}
	function firstMessage(entry) {
		if (entry == null) return "";
		if (typeof entry === "string") return entry;
		if (Array.isArray(entry)) return firstMessage(entry[0]);
		if (typeof entry === "object") {
			const hit = Object.entries(entry).find(([k]) => k !== "label" && k !== "form");
			return hit ? firstMessage(hit[1]) : "";
		}
		return String(entry);
	}
	/**
	* Normalise an error payload into FormIssue[]. Accepts:
	*  - a plain string            → [{ message }]
	*  - a string[]                → one issue each
	*  - a FormIssue[]             → passthrough (already normalised)
	*  - `{ field: "message" }`    → [{ label: field, message }]   (e.g. newsletter `errors`)
	*  - MelisCore formatErrors    → `{ massd_text: { isEmpty: "…", label: "Input Label" } }`
	*                                → [{ label: "Input Label", message: "…" }]
	* The optional `labels` map renames a raw field key to a display label (server key → UI label).
	*/
	function collectIssues(input, labels = {}) {
		if (input == null || input === "") return [];
		if (typeof input === "string") return [{ message: input }];
		if (Array.isArray(input)) return input.map((v) => typeof v === "string" ? { message: v } : v).filter((i) => i && (i.message || i.label));
		if (typeof input === "object") {
			const out = [];
			for (const [field, entry] of Object.entries(input)) {
				if (field === "label" || field === "form" || entry == null) continue;
				const message = firstMessage(entry);
				if (!message) continue;
				const entryLabel = entry && typeof entry === "object" ? entry.label : void 0;
				out.push({
					label: labels[field] ?? entryLabel ?? field,
					message
				});
			}
			return out;
		}
		return [];
	}
	var box = {
		border: "1px solid color-mix(in srgb, #ef4444 45%, var(--color-border,#e5e7eb))",
		background: "color-mix(in srgb, #ef4444 10%, var(--color-card,#fff))",
		color: "#dc2626",
		borderRadius: 8,
		padding: "10px 14px",
		fontSize: 14,
		lineHeight: 1.45
	};
	var listCss = {
		margin: "6px 0 0",
		padding: "0 0 0 18px",
		display: "flex",
		flexDirection: "column",
		gap: 2
	};
	/**
	* Standard form-error banner. Show it above a form/modal on a failed save/submit.
	*  - `title`   headline (caller-provided → i18n stays with the caller). Defaults to a generic English
	*              line; every real caller should pass its own translated string.
	*  - `issues`  the missing/invalid fields to list. Pass anything `collectIssues` accepts OR a
	*              ready FormIssue[]; a bare string is treated as a single message.
	*  - `icon`    optional leading node (e.g. an alert glyph).
	*  - `html`    when set, the caller vouches that `title` and each issue `message` carry TRUSTED
	*              HTML (e.g. Melis service messages that embed `<b>path</b>`) → the markup is rendered
	*              instead of escaped. Default false (safe text). Labels are our own i18n and are always
	*              rendered as text. Only pass `html` for server/legacy messages you know are trusted —
	*              it is a dangerouslySetInnerHTML sink; never enable it for free user input.
	* When there are no issues and no title, renders nothing.
	*/
	function FormErrorBanner({ title, issues, icon, html, style }) {
		const list = collectIssues(issues);
		if (!title && list.length === 0) return null;
		const headline = title ?? "Please check the required fields.";
		const renderText = (value, s) => html ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
			style: s,
			dangerouslySetInnerHTML: { __html: value }
		}) : /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
			style: s,
			children: value
		});
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
			role: "alert",
			style: {
				...box,
				...style
			},
			children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					display: "flex",
					alignItems: "flex-start",
					gap: 8
				},
				children: [icon != null && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
					style: {
						flexShrink: 0,
						lineHeight: 1.4
					},
					children: icon
				}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						flex: 1,
						minWidth: 0
					},
					children: [headline && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
						style: { fontWeight: 600 },
						children: renderText(headline)
					}), list.length > 0 && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("ul", {
						style: listCss,
						children: list.map((it, i) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("li", {
							style: { fontSize: 13 },
							children: [it.label && /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("span", {
								style: { fontWeight: 600 },
								children: [it.label, it.message ? " — " : ""]
							}), it.message && renderText(it.message)]
						}, i))
					})]
				})]
			})
		});
	}
	//#endregion
	//#region src/SettingsPanel.tsx
	/**
	* Onglet « Paramètres » natif React de l'outil Site Analytics.
	*
	* Équivalent de la vue legacy `tool-content-container-analytics-settings-tab-content.phtml` :
	*   Site → Module analytics → (réglages propres au module) → script JS personnalisé → Enregistrer.
	*
	* Deux principes (cf. ai-skills / melis-migrate-module-to-react) :
	*  • ZÉRO logique métier ici. L'enregistrement poste sur l'action LEGACY `.../save`, qui garde la
	*    validation Laminas, l'upload de clé privée, la sérialisation de pads_settings, la garde admin
	*    sur le JS brut et le flash messenger. Les vues New et Old restent donc rigoureusement alignées.
	*  • DATA-DRIVEN. Les modules ET leurs champs viennent de la config plateforme
	*    (meliscms/datas/page_analytics + meliscms/forms/<key>_settings_form) : un module analytics
	*    tiers (ex. MelisCmsGoogleAnalytics) apparaît sans toucher à cette brique.
	*/
	var label = {
		display: "block",
		fontSize: 13,
		fontWeight: 600,
		marginBottom: 6
	};
	var help = {
		fontSize: 12,
		color: "var(--color-muted-foreground)",
		margin: "6px 0 0"
	};
	var errCss = {
		fontSize: 12,
		color: "var(--color-destructive,#dc2626)",
		margin: "6px 0 0"
	};
	function SettingsPanel() {
		const t = useT();
		const narrow = useIsNarrow();
		const [sites, setSites] = (0, react.useState)([]);
		const [site, setSite] = (0, react.useState)(0);
		const [state, setState] = (0, react.useState)(null);
		const [moduleKey, setModuleKey] = (0, react.useState)("");
		const [values, setValues] = (0, react.useState)({});
		const [files, setFiles] = (0, react.useState)({});
		const [js, setJs] = (0, react.useState)("");
		const [saving, setSaving] = (0, react.useState)(false);
		const [errors, setErrors] = (0, react.useState)({});
		const [formError, setFormError] = (0, react.useState)(null);
		const [flash, setFlash] = (0, react.useState)(null);
		(0, react.useEffect)(() => {
			fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null);
		}, []);
		const load = (siteId, key) => {
			if (!siteId) {
				setState(null);
				return;
			}
			fetchAnalyticsSettings(siteId, key).then((s) => {
				setState(s);
				setModuleKey(s.selectedKey);
				setValues(Object.fromEntries(Object.entries(s.values ?? {}).map(([k, v]) => [k, String(v ?? "")])));
				setJs(s.jsAnalytics);
				setFiles({});
				setErrors({});
				setFormError(null);
			}).catch(() => setState(null));
		};
		(0, react.useEffect)(() => {
			load(site);
		}, [site]);
		const onModule = (key) => {
			setModuleKey(key);
			setErrors({});
			setFormError(null);
			if (site && key) load(site, key);
		};
		const selected = state?.modules.find((m) => m.key === moduleKey);
		const fields = state?.fields ?? [];
		const showJs = !!selected?.settings;
		async function submit(e) {
			e.preventDefault();
			if (!site || !moduleKey) return;
			setSaving(true);
			setErrors({});
			setFormError(null);
			setFlash(null);
			const fd = new FormData();
			fd.append("pad_site_id", String(site));
			fd.append("pad_analytics_key", moduleKey);
			for (const f of fields) {
				if (f.type === "file") continue;
				fd.append(f.name, values[f.name] ?? "");
			}
			for (const [name, file] of Object.entries(files)) fd.append(name, file);
			fd.append("pads_js_analytics", showJs ? js : "");
			fd.append("fileChanged", Object.keys(files).length > 0 ? "true" : "false");
			try {
				const r = await saveAnalyticsSettings(fd);
				if (r.success) {
					setFlash({
						ok: true,
						msg: r.textMessage
					});
					okNotify(t("set_save"), r.textMessage);
					load(site, moduleKey);
				} else {
					const flat = {};
					for (const [name, msgs] of Object.entries(r.errors ?? {})) {
						const first = Object.entries(msgs ?? {}).find(([k]) => k !== "label");
						if (first) flat[name] = String(first[1]);
					}
					setErrors(flat);
					setFormError(r.textMessage || t("set_check_fields"));
					koNotify(t("set_error"), r.textMessage || "");
				}
			} catch {
				setFormError(t("set_error"));
				koNotify(t("set_error"));
			} finally {
				setSaving(false);
			}
		}
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("form", {
			onSubmit: submit,
			style: {
				display: "flex",
				flexDirection: "column",
				gap: 20,
				padding: narrow ? 16 : 24,
				boxSizing: "border-box",
				maxWidth: 760
			},
			children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					...card$1,
					display: "flex",
					flexDirection: "column",
					gap: narrow ? 16 : 18,
					padding: narrow ? 14 : 20
				},
				children: [
					(formError || Object.keys(errors).length > 0) && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(FormErrorBanner, {
						title: formError ?? t("set_check_fields"),
						issues: collectIssues(errors, {
							pad_analytics_key: t("set_module"),
							pads_js_analytics: t("set_js"),
							...Object.fromEntries(fields.map((f) => [f.name, f.label]))
						})
					}),
					/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("label", {
						style: label,
						htmlFor: "mcpa-site",
						children: t("set_site")
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("select", {
						id: "mcpa-site",
						style: inputCss,
						value: site,
						onChange: (e) => setSite(Number(e.target.value)),
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
							value: 0,
							children: t("set_site_ph")
						}), sites.map((s) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
							value: s.id,
							children: s.name
						}, s.id))]
					})] }),
					!site && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
						style: help,
						children: t("set_pick_site")
					}),
					site > 0 && state && /* @__PURE__ */ (0, react_jsx_runtime.jsxs)(react_jsx_runtime.Fragment, { children: [
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [
							/* @__PURE__ */ (0, react_jsx_runtime.jsx)("label", {
								style: label,
								htmlFor: "mcpa-module",
								children: t("set_module")
							}),
							/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("select", {
								id: "mcpa-module",
								style: inputCss,
								value: moduleKey,
								onChange: (e) => onModule(e.target.value),
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
									value: "",
									children: t("set_module_ph")
								}), state.modules.map((m) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
									value: m.key,
									children: m.label
								}, m.key))]
							}),
							/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: help,
								children: t("set_module_help")
							}),
							errors.pad_analytics_key && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: errCss,
								children: errors.pad_analytics_key
							})
						] }),
						fields.map((f) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [
							/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("label", {
								style: label,
								htmlFor: `mcpa-${f.name}`,
								children: [f.label, f.required ? " *" : ""]
							}),
							f.type === "textarea" ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("textarea", {
								id: `mcpa-${f.name}`,
								style: {
									...inputCss,
									height: 100,
									padding: 10,
									fontFamily: "inherit"
								},
								value: values[f.name] ?? "",
								onChange: (e) => setValues((v) => ({
									...v,
									[f.name]: e.target.value
								}))
							}) : f.type === "select" ? /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("select", {
								id: `mcpa-${f.name}`,
								style: inputCss,
								value: values[f.name] ?? "",
								onChange: (e) => setValues((v) => ({
									...v,
									[f.name]: e.target.value
								})),
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", { value: "" }), f.options.map((o) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
									value: o.value,
									children: o.label
								}, o.value))]
							}) : f.type === "file" ? /* @__PURE__ */ (0, react_jsx_runtime.jsxs)(react_jsx_runtime.Fragment, { children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("input", {
								id: `mcpa-${f.name}`,
								type: "file",
								style: {
									...inputCss,
									height: "auto",
									padding: 8
								},
								onChange: (e) => {
									const file = e.target.files?.[0];
									setFiles((prev) => {
										const next = { ...prev };
										if (file) next[f.name] = file;
										else delete next[f.name];
										return next;
									});
								}
							}), values[f.name] && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: help,
								children: t("set_current_file", { n: values[f.name] })
							})] }) : /* @__PURE__ */ (0, react_jsx_runtime.jsx)("input", {
								id: `mcpa-${f.name}`,
								type: f.type === "password" ? "password" : "text",
								style: inputCss,
								value: values[f.name] ?? "",
								onChange: (e) => setValues((v) => ({
									...v,
									[f.name]: e.target.value
								}))
							}),
							f.tooltip && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: help,
								children: f.tooltip
							}),
							errors[f.name] && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: errCss,
								children: errors[f.name]
							})
						] }, f.name)),
						moduleKey && moduleKey !== "melis_cms_no_analytics" && fields.length === 0 && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
							style: help,
							children: t("set_no_settings")
						}),
						showJs && /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [
							/* @__PURE__ */ (0, react_jsx_runtime.jsx)("label", {
								style: label,
								htmlFor: "mcpa-js",
								children: t("set_js")
							}),
							/* @__PURE__ */ (0, react_jsx_runtime.jsx)("textarea", {
								id: "mcpa-js",
								style: {
									...inputCss,
									height: 160,
									padding: 10,
									fontFamily: "ui-monospace,SFMono-Regular,Menlo,monospace",
									fontSize: 13,
									lineHeight: 1.5
								},
								spellCheck: false,
								value: js,
								disabled: !state.jsEditable,
								onChange: (e) => setJs(e.target.value)
							}),
							/* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: help,
								children: state.jsEditable ? t("set_js_help") : t("set_js_admin")
							}),
							errors.pads_js_analytics && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
								style: errCss,
								children: errors.pads_js_analytics
							})
						] }),
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
							style: {
								display: "flex",
								alignItems: narrow ? "stretch" : "center",
								flexDirection: narrow ? "column" : "row",
								gap: 12
							},
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
								type: "submit",
								style: {
									...btnPrimary$1,
									...narrow ? { justifyContent: "center" } : {},
									opacity: saving || !moduleKey ? .6 : 1
								},
								disabled: saving || !moduleKey,
								children: saving ? t("set_saving") : t("set_save")
							}), flash && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
								style: {
									fontSize: 13,
									color: flash.ok ? "var(--color-success,#16a34a)" : "var(--color-destructive,#dc2626)"
								},
								children: flash.msg
							})]
						})
					] })
				]
			})
		});
	}
	//#endregion
	//#region src/shared/ExpandableRow.tsx
	/**
	* Per-row "+" toggle (leftmost column of a table) that reveals the columns currently hidden
	* via column collapse on narrow viewports — same visibility source as the desktop ColManager,
	* just surfaced per-row. Pair with <HiddenColsRow>. Inline styles only — a brick can't use the
	* host's Tailwind classes.
	*/
	var sIcon = {
		width: 13,
		height: 13,
		flexShrink: 0
	};
	var PlusIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("svg", {
		style: sIcon,
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M12 5v14M5 12h14" })
	});
	var MinusIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("svg", {
		style: sIcon,
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M5 12h14" })
	});
	function ExpandToggle({ expanded, onClick }) {
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
			type: "button",
			onClick,
			"aria-expanded": expanded,
			style: {
				display: "inline-flex",
				alignItems: "center",
				justifyContent: "center",
				width: 24,
				height: 24,
				borderRadius: 6,
				border: "1px solid var(--color-border)",
				background: "transparent",
				color: "var(--color-muted-foreground)",
				cursor: "pointer",
				padding: 0
			},
			children: expanded ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)(MinusIcon, {}) : /* @__PURE__ */ (0, react_jsx_runtime.jsx)(PlusIcon, {})
		});
	}
	/**
	* Detail row shown under an expanded row — one label/value pair per hidden column.
	* Two columns side by side on desktop; a single stacked column on narrow viewports (a 2-col
	* grid there fights for width against wrapped long values).
	*/
	function HiddenColsRow({ cols, labelFor, renderValue, colSpan, narrow }) {
		const hidden = cols.filter((c) => !c.visible);
		if (hidden.length === 0) return null;
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tr", { children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
			colSpan,
			style: {
				padding: "10px 16px",
				borderTop: "1px solid var(--color-border)",
				background: "var(--color-muted,rgba(0,0,0,.02))",
				width: 0
			},
			children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
				style: {
					display: "grid",
					gridTemplateColumns: !narrow && hidden.length > 1 ? "repeat(2, minmax(0, 1fr))" : "minmax(0, 1fr)",
					columnGap: 24,
					rowGap: 10
				},
				children: hidden.map((c) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "grid",
						gridTemplateColumns: "auto minmax(0, 1fr)",
						alignItems: "baseline",
						gap: 8,
						fontSize: 13
					},
					children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("span", {
						style: {
							fontSize: 11,
							fontWeight: 600,
							textTransform: "uppercase",
							letterSpacing: ".04em",
							color: "var(--color-muted-foreground)"
						},
						children: [labelFor(c.id), ":"]
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
						style: {
							minWidth: 0,
							maxWidth: 220,
							overflowWrap: "break-word",
							display: "-webkit-box",
							WebkitLineClamp: 2,
							WebkitBoxOrient: "vertical",
							overflow: "hidden"
						},
						children: renderValue(c.id)
					})]
				}, c.id))
			})
		}) });
	}
	//#endregion
	//#region src/PageAnalyticsPage.tsx
	var MELIS_KEY = "meliscms_page_analytics_display";
	/** Icône de tri unifiée — mêmes tracés que les icônes lucide ArrowUpDown/ArrowUp/ArrowDown du core. */
	function SortIcon({ dir }) {
		const p = {
			width: 12,
			height: 12,
			viewBox: "0 0 24 24",
			fill: "none",
			stroke: "currentColor",
			strokeWidth: 2,
			strokeLinecap: "round",
			strokeLinejoin: "round",
			style: {
				flexShrink: 0,
				opacity: dir ? 1 : .3
			}
		};
		if (dir === "asc") return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
			...p,
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m5 12 7-7 7 7" }), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M12 19V5" })]
		});
		if (dir === "desc") return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
			...p,
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M12 5v14" }), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m19 12-7 7-7-7" })]
		});
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
			...p,
			children: [
				/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m21 16-4 4-4-4" }),
				/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M17 20V4" }),
				/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m3 8 4-4 4 4" }),
				/* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "M7 4v16" })
			]
		});
	}
	/** Onglets natifs « Analytics » / « Paramètres » — même découpage que l'outil legacy. */
	function TabBar({ tab, onChange, narrow }) {
		const t = useT();
		const item = (id, text) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
			type: "button",
			onClick: () => onChange(id),
			style: {
				appearance: "none",
				border: 0,
				background: "transparent",
				cursor: "pointer",
				padding: "10px 4px",
				fontSize: 14,
				fontWeight: tab === id ? 600 : 500,
				color: tab === id ? "var(--color-foreground)" : "var(--color-muted-foreground)",
				borderBottom: `2px solid ${tab === id ? "var(--color-primary)" : "transparent"}`
			},
			children: text
		}, id);
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "flex",
				gap: narrow ? 16 : 20,
				flexWrap: narrow ? "wrap" : "nowrap",
				padding: narrow ? "0 16px" : "0 24px",
				borderBottom: "1px solid var(--color-border)",
				flexShrink: 0
			},
			children: [item("analytics", t("tab_analytics")), item("settings", t("tab_settings"))]
		});
	}
	function PageAnalyticsPage() {
		const t = useT();
		const narrow = useIsNarrow();
		const [mode, setMode] = (0, react.useState)("react");
		const [tab, setTab] = (0, react.useState)("analytics");
		const [frameLoaded, setFrameLoaded] = (0, react.useState)(false);
		const [site, setSite] = (0, react.useState)(0);
		const subtitle = mode === "iframe" ? t("old_hint") : tab === "settings" ? t("set_subtitle") : t("subtitle");
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "flex",
				flexDirection: "column",
				height: "100%"
			},
			children: [
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "flex",
						alignItems: "center",
						justifyContent: "space-between",
						gap: narrow ? 8 : 16,
						flexWrap: narrow ? "nowrap" : "wrap",
						padding: narrow ? "16px 16px 10px" : "20px 24px 12px",
						flexShrink: 0
					},
					children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
						style: narrow ? { minWidth: 0 } : void 0,
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("h1", {
							style: {
								fontSize: narrow ? 17 : 20,
								fontWeight: 700,
								margin: 0,
								...narrow ? {
									overflow: "hidden",
									textOverflow: "ellipsis",
									whiteSpace: "nowrap"
								} : {}
							},
							children: t("title")
						}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
							style: {
								fontSize: narrow ? 12 : 14,
								color: "var(--color-muted-foreground)",
								margin: "2px 0 0",
								...narrow ? {
									overflow: "hidden",
									textOverflow: "ellipsis",
									whiteSpace: "nowrap"
								} : {}
							},
							children: subtitle
						})]
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
						style: { flexShrink: 0 },
						children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ViewToggle, {
							mode,
							compact: narrow,
							onChange: (m) => {
								setMode(m);
								if (m === "iframe") setFrameLoaded(true);
							}
						})
					})]
				}),
				mode === "react" && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(TabBar, {
					tab,
					onChange: setTab,
					narrow
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						flex: 1,
						minHeight: 0,
						position: "relative"
					},
					children: [
						frameLoaded && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								position: "absolute",
								inset: 0,
								display: mode === "iframe" ? "block" : "none"
							},
							children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("iframe", {
								src: `/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`,
								style: {
									width: "100%",
									height: "100%",
									border: 0
								},
								title: `${t("title")} — Vue Melis`
							})
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								position: "absolute",
								inset: 0,
								overflow: "auto",
								display: mode === "react" && tab === "analytics" ? "block" : "none"
							},
							children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)(AnalyticsList, {
								site,
								onSite: setSite,
								narrow
							})
						}),
						mode === "react" && tab === "settings" && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								position: "absolute",
								inset: 0,
								overflow: "auto"
							},
							children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)(SettingsPanel, {})
						})
					]
				})
			]
		});
	}
	var COL_LABEL = {
		pageId: "col_pageId",
		pageName: "col_pageName",
		count: "col_count",
		lastVisit: "col_last"
	};
	var colStore = makeColStore("melis-page-analytics-cols-v1", [
		{
			id: "pageId",
			visible: true
		},
		{
			id: "pageName",
			visible: true
		},
		{
			id: "count",
			visible: true
		},
		{
			id: "lastVisit",
			visible: true
		}
	]);
	/** Colonnes conservées sur viewport étroit ; les autres passent dans la ligne dépliable « + ».
	*  Pas de colonne d'actions ici → deux essentielles tiennent (la page ET son nombre de visites,
	*  sinon la table ne dit plus rien). */
	var ESSENTIAL_COLS = new Set(["pageName", "count"]);
	function AnalyticsList({ site, onSite, narrow }) {
		const t = useT();
		const lang = currentLang$1();
		const [stats, setStats] = (0, react.useState)(null);
		const [sites, setSites] = (0, react.useState)([]);
		const [search, setSearch] = (0, react.useState)("");
		const [cols, setCols] = (0, react.useState)(colStore.load);
		const [showCols, setShowCols] = (0, react.useState)(false);
		const [showExport, setShowExport] = (0, react.useState)(false);
		const [tick, setTick] = (0, react.useState)(0);
		const [expanded, setExpanded] = (0, react.useState)(() => /* @__PURE__ */ new Set());
		const colsAnchorRef = (0, react.useRef)(null);
		const displayCols = narrow ? cols.map((c) => ({
			...c,
			visible: ESSENTIAL_COLS.has(c.id)
		})) : cols;
		const hasHidden = narrow;
		const shownCols = visibleCols(displayCols);
		const { items, total, loading, hasMore, sentinelRef, sortCol, sortDir, toggleSort } = useKeysetList({
			fetcher: (a) => fetchAnalytics({
				search,
				site,
				limit: a.limit,
				sort: a.sort,
				dir: a.dir,
				after: a.after ?? void 0
			}).then((r) => ({
				items: r.items,
				total: r.total,
				nextCursor: r.nextCursor
			})),
			deps: [
				search,
				site,
				tick
			],
			defaultSort: "count",
			defaultDir: "desc"
		});
		(0, react.useEffect)(() => {
			fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null);
		}, []);
		(0, react.useEffect)(() => {
			fetchAnalyticsStats({
				search,
				site
			}).then(setStats).catch(() => null);
		}, [
			search,
			site,
			tick
		]);
		const cell = (r, id) => {
			switch (id) {
				case "pageId": return String(r.pageId);
				case "pageName": return r.pageName || t("deleted");
				case "count": return String(r.count);
				case "lastVisit": return fmtDate(r.lastVisit, lang);
				default: return "";
			}
		};
		const fetchAll = async () => {
			const acc = [];
			let after = null;
			for (;;) {
				const r = await fetchAnalytics({
					search,
					site,
					sort: sortCol,
					dir: sortDir,
					limit: 100,
					after
				});
				acc.push(...r.items);
				if (!r.nextCursor) break;
				after = r.nextCursor;
			}
			return acc;
		};
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "flex",
				flexDirection: "column",
				gap: narrow ? 16 : 20,
				padding: narrow ? 16 : 24,
				boxSizing: "border-box"
			},
			children: [
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "flex",
						gap: 12,
						flexWrap: "wrap"
					},
					children: [
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_hits"),
							value: stats?.hits ?? null,
							narrow
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_pages"),
							value: stats?.pages ?? null,
							narrow
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_sites"),
							value: stats?.sites ?? null,
							narrow
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_last"),
							value: stats ? fmtDate(stats.lastVisit, lang) : null,
							narrow
						})
					]
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						display: "flex",
						gap: 8,
						flexWrap: "wrap",
						alignItems: "center"
					},
					children: [
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("select", {
							style: {
								...inputCss,
								height: 36,
								...narrow ? { width: "100%" } : {
									width: "auto",
									minWidth: 180
								}
							},
							value: site,
							onChange: (e) => onSite(Number(e.target.value)),
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("option", {
								value: 0,
								children: t("site_all")
							}), sites.map((s) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("option", {
								value: s.id,
								children: [
									s.name,
									" (#",
									s.id,
									")"
								]
							}, s.id))]
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)("input", {
							style: {
								...inputCss,
								height: 36,
								...narrow ? { width: "100%" } : {
									flex: 1,
									minWidth: 200
								}
							},
							value: search,
							onChange: (e) => setSearch(e.target.value),
							placeholder: t("search")
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
							style: {
								display: "flex",
								gap: 8,
								alignItems: "center",
								...narrow ? { width: "100%" } : {}
							},
							children: [
								/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
									ref: colsAnchorRef,
									style: {
										position: "relative",
										...narrow ? {
											flex: "1 1 0",
											minWidth: 0
										} : {}
									},
									children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
										style: {
											...btnGhost$1,
											height: 36,
											...narrow ? {
												width: "100%",
												justifyContent: "center"
											} : {}
										},
										onClick: () => setShowCols((v) => !v),
										children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(GripIcon$1, {}), t("columns")]
									}), showCols && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ColManager, {
										anchorRef: colsAnchorRef,
										cols,
										labelFor: (id) => t(COL_LABEL[id]),
										onChange: setCols,
										onSave: colStore.save,
										defaults: colStore.defaults,
										onClose: () => setShowCols(false)
									})]
								}),
								/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
									style: {
										...btnGhost$1,
										height: 36,
										...narrow ? {
											flex: "1 1 0",
											minWidth: 0,
											justifyContent: "center"
										} : {}
									},
									onClick: () => setShowExport(true),
									children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(DownloadIcon, {}), t("export")]
								}),
								/* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
									style: {
										...btnGhost$1,
										height: 36,
										flexShrink: 0
									},
									onClick: () => setTick((x) => x + 1),
									title: t("refresh"),
									children: "↻"
								})
							]
						})
					]
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						...card$1,
						overflow: "hidden"
					},
					children: [
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("table", {
							style: {
								width: "100%",
								borderCollapse: "collapse",
								...narrow ? {} : { minWidth: 560 }
							},
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("thead", {
								style: { background: "var(--color-muted,rgba(0,0,0,.03))" },
								children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("tr", { children: [hasHidden && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("th", { style: {
									...th,
									width: 40,
									padding: "10px 8px 10px 12px"
								} }), shownCols.map(({ id }) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("th", {
									style: {
										...th,
										cursor: "pointer",
										...sortCol === id ? { color: "var(--color-primary)" } : {}
									},
									onClick: () => toggleSort(id),
									children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("span", {
										style: {
											display: "inline-flex",
											alignItems: "center",
											gap: 4
										},
										children: [t(COL_LABEL[id]), /* @__PURE__ */ (0, react_jsx_runtime.jsx)(SortIcon, { dir: sortCol === id ? sortDir : null })]
									})
								}, id))] })
							}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tbody", { children: items.length === 0 && !loading ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tr", { children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
								style: {
									...td,
									textAlign: "center",
									color: "var(--color-muted-foreground)",
									padding: "40px 16px"
								},
								colSpan: shownCols.length + (hasHidden ? 1 : 0),
								children: t("empty")
							}) }) : items.map((r) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)(react.Fragment, { children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("tr", { children: [hasHidden && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
								style: {
									...td,
									width: 40,
									padding: "10px 8px 10px 12px"
								},
								children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ExpandToggle, {
									expanded: expanded.has(r.pageId),
									onClick: () => setExpanded((prev) => {
										const next = new Set(prev);
										if (!next.delete(r.pageId)) next.add(r.pageId);
										return next;
									})
								})
							}), shownCols.map(({ id }) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
								style: {
									...td,
									...id === "pageId" || id === "count" ? {
										color: "var(--color-muted-foreground)",
										fontVariantNumeric: "tabular-nums"
									} : {}
								},
								children: id === "pageName" && !r.pageName ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
									style: {
										fontStyle: "italic",
										color: "var(--color-muted-foreground)"
									},
									children: t("deleted")
								}) : cell(r, id)
							}, id))] }), hasHidden && expanded.has(r.pageId) && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(HiddenColsRow, {
								cols: displayCols,
								labelFor: (id) => t(COL_LABEL[id]),
								narrow,
								colSpan: shownCols.length + 1,
								renderValue: (id) => id === "pageName" && !r.pageName ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
									style: {
										fontStyle: "italic",
										color: "var(--color-muted-foreground)"
									},
									children: t("deleted")
								}) : cell(r, id)
							})] }, r.pageId)) })]
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							ref: sentinelRef,
							style: { height: 1 }
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
							style: {
								padding: "10px 16px",
								textAlign: "center",
								fontSize: 12,
								color: "var(--color-muted-foreground)"
							},
							children: loading ? t("loading") : !hasMore && items.length > 0 ? t("count", { n: total }) : ""
						})
					]
				}),
				showExport && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ExportModal, {
					cols,
					labelFor: (id) => t(COL_LABEL[id]),
					fetchAll,
					getCell: (r, id) => cell(r, id),
					filename: "page-analytics",
					sheetName: t("title"),
					total,
					onClose: () => setShowExport(false)
				})
			]
		});
	}
	//#endregion
	//#region src/brick.tsx
	window.__melisRegisterBrick?.({
		id: "pageanalytics",
		Component: PageAnalyticsPage
	});
	//#endregion
})(MelisReact, MelisReactJsxRuntime);

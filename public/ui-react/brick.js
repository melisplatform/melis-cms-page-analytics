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
		for (const [k, v] of Object.entries(params)) if (v !== void 0 && v !== "" && v !== 0) sp.set(k, String(v));
		const s = sp.toString();
		return s ? `?${s}` : "";
	}
	function fetchAnalytics(p = {}) {
		return apiGet(`/melis/react-api/page-analytics${qs({
			page: p.page,
			limit: p.limit,
			search: p.search,
			site: p.site,
			sort: p.sort,
			dir: p.dir
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
			detail_count: "{n} visites — fin de la liste"
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
			detail_count: "{n} visits — end of list"
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
	function Kpi({ label: lbl, value }) {
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				...card$1,
				display: "flex",
				flexDirection: "column",
				gap: 2,
				padding: 16,
				flex: 1,
				minWidth: 140
			},
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
				style: {
					fontSize: 12,
					color: "var(--color-muted-foreground)"
				},
				children: lbl
			}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("span", {
				style: {
					fontSize: 22,
					fontWeight: 700
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
	function ColManager({ cols, labelFor, onChange, onSave, defaults, onClose }) {
		const t = useT();
		const [dragId, setDragId] = (0, react.useState)(null);
		const [over, setOver] = (0, react.useState)(null);
		const shown = cols.filter((c) => c.visible);
		const hidden = cols.filter((c) => !c.visible);
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
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				...card$1,
				position: "absolute",
				right: 0,
				top: "100%",
				marginTop: 6,
				zIndex: 50,
				width: 380,
				maxWidth: "calc(100vw - 1rem)"
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
				background: "rgba(0,0,0,.5)"
			},
			onClick: (e) => {
				if (e.target === e.currentTarget) onClose();
			},
			children: /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					...card,
					width: "100%",
					maxWidth: 480
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
								gridTemplateColumns: "1fr 1fr",
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
	var sIcon = {
		width: 15,
		height: 15,
		flexShrink: 0
	};
	var SparkIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("svg", {
		style: sIcon,
		viewBox: "0 0 24 24",
		fill: "none",
		stroke: "currentColor",
		strokeWidth: "2",
		strokeLinecap: "round",
		strokeLinejoin: "round",
		children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("path", { d: "m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z" })
	});
	var LayoutIcon = () => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("svg", {
		style: sIcon,
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
	function ViewToggle({ mode, onChange }) {
		const tab = (active) => ({
			display: "inline-flex",
			alignItems: "center",
			gap: 6,
			height: 30,
			padding: "0 12px",
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
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(SparkIcon, {}), "New"]
			}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
				style: tab(mode === "iframe"),
				onClick: () => onChange("iframe"),
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(LayoutIcon, {}), "Old"]
			})]
		});
	}
	//#endregion
	//#region src/PageAnalyticsPage.tsx
	/**
	* Outil Site Analytics (brique MelisCmsPageAnalytics).
	* En-tête PERSISTANT (titre + toggle New/Old) → le toggle reste toujours accessible.
	*  • « New » : table React native des visites par page (sélecteur de site, KPI, recherche,
	*    colonnes, export). Lecture seule, sans drill-down (fidèle au legacy).
	*  • « Old » : outil legacy complet en iframe — onglets « Analytics » + « Paramètres »
	*    (affectation du module analytics aux sites, config GA/JS). Non migré, géré en legacy.
	* Brique `persistent` : état + iframe préservés en changeant d'onglet outil.
	*/
	var MELIS_KEY = "meliscms_page_analytics_display";
	function PageAnalyticsPage() {
		const t = useT();
		const [mode, setMode] = (0, react.useState)("react");
		const [frameLoaded, setFrameLoaded] = (0, react.useState)(false);
		const [site, setSite] = (0, react.useState)(0);
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "flex",
				flexDirection: "column",
				height: "100%"
			},
			children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					display: "flex",
					alignItems: "center",
					justifyContent: "space-between",
					gap: 16,
					flexWrap: "wrap",
					padding: "20px 24px 12px",
					flexShrink: 0
				},
				children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("h1", {
					style: {
						fontSize: 20,
						fontWeight: 700,
						margin: 0
					},
					children: t("title")
				}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("p", {
					style: {
						fontSize: 14,
						color: "var(--color-muted-foreground)",
						margin: "2px 0 0"
					},
					children: mode === "iframe" ? t("old_hint") : t("subtitle")
				})] }), /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ViewToggle, {
					mode,
					onChange: (m) => {
						setMode(m);
						if (m === "iframe") setFrameLoaded(true);
					}
				})]
			}), /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
				style: {
					flex: 1,
					minHeight: 0,
					position: "relative"
				},
				children: [frameLoaded && /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
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
				}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
					style: {
						position: "absolute",
						inset: 0,
						overflow: "auto",
						display: mode === "react" ? "block" : "none"
					},
					children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)(AnalyticsList, {
						site,
						onSite: setSite
					})
				})]
			})]
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
	function AnalyticsList({ site, onSite }) {
		const t = useT();
		const lang = currentLang$1();
		const [rows, setRows] = (0, react.useState)([]);
		const [stats, setStats] = (0, react.useState)(null);
		const [sites, setSites] = (0, react.useState)([]);
		const [loading, setLoading] = (0, react.useState)(false);
		const [search, setSearch] = (0, react.useState)("");
		const [sortCol, setSortCol] = (0, react.useState)("count");
		const [sortDir, setSortDir] = (0, react.useState)("desc");
		const [cols, setCols] = (0, react.useState)(colStore.load);
		const [showCols, setShowCols] = (0, react.useState)(false);
		const [showExport, setShowExport] = (0, react.useState)(false);
		const [tick, setTick] = (0, react.useState)(0);
		(0, react.useEffect)(() => {
			fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null);
		}, []);
		(0, react.useEffect)(() => {
			fetchAnalyticsStats({ site }).then(setStats).catch(() => null);
		}, [site, tick]);
		(0, react.useEffect)(() => {
			setLoading(true);
			fetchAnalytics({
				site,
				limit: 9999
			}).then((r) => setRows(r.items)).catch(() => null).finally(() => setLoading(false));
		}, [site, tick]);
		const cell = (r, id) => {
			switch (id) {
				case "pageId": return String(r.pageId);
				case "pageName": return r.pageName || t("deleted");
				case "count": return String(r.count);
				case "lastVisit": return fmtDate(r.lastVisit, lang);
				default: return "";
			}
		};
		const sortVal = (r, id) => {
			switch (id) {
				case "pageId": return r.pageId;
				case "count": return r.count;
				case "lastVisit": return r.lastVisit ?? "";
				default: return (r.pageName || "").toLowerCase();
			}
		};
		const filtered = (0, react.useMemo)(() => {
			const q = search.trim().toLowerCase();
			let list = rows;
			if (q) list = rows.filter((r) => (r.pageName || "").toLowerCase().includes(q) || String(r.pageId).includes(q));
			const dir = sortDir === "asc" ? 1 : -1;
			return [...list].sort((a, b) => {
				const va = sortVal(a, sortCol), vb = sortVal(b, sortCol);
				if (va < vb) return -1 * dir;
				if (va > vb) return 1 * dir;
				return 0;
			});
		}, [
			rows,
			search,
			sortCol,
			sortDir
		]);
		function toggleSort(id) {
			if (sortCol === id) setSortDir((d) => d === "asc" ? "desc" : "asc");
			else {
				setSortCol(id);
				setSortDir(id === "pageName" ? "asc" : "desc");
			}
		}
		return /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
			style: {
				display: "flex",
				flexDirection: "column",
				gap: 20,
				padding: 24,
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
							value: stats?.hits ?? null
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_pages"),
							value: stats?.pages ?? null
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_sites"),
							value: stats?.sites ?? null
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)(Kpi, {
							label: t("kpi_last"),
							value: stats ? fmtDate(stats.lastVisit, lang) : null
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
								width: "auto",
								minWidth: 180
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
								flex: 1,
								minWidth: 200
							},
							value: search,
							onChange: (e) => setSearch(e.target.value),
							placeholder: t("search")
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
							style: { position: "relative" },
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("button", {
								style: {
									...btnGhost$1,
									height: 36
								},
								onClick: () => setShowCols((v) => !v),
								children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(GripIcon$1, {}), t("columns")]
							}), showCols && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ColManager, {
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
								height: 36
							},
							onClick: () => setShowExport(true),
							children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)(DownloadIcon, {}), t("export")]
						}),
						/* @__PURE__ */ (0, react_jsx_runtime.jsx)("button", {
							style: {
								...btnGhost$1,
								height: 36
							},
							onClick: () => setTick((x) => x + 1),
							title: t("refresh"),
							children: "↻"
						})
					]
				}),
				/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("div", {
					style: {
						...card$1,
						overflow: "hidden"
					},
					children: [/* @__PURE__ */ (0, react_jsx_runtime.jsxs)("table", {
						style: {
							width: "100%",
							borderCollapse: "collapse",
							minWidth: 560
						},
						children: [/* @__PURE__ */ (0, react_jsx_runtime.jsx)("thead", {
							style: { background: "var(--color-muted,rgba(0,0,0,.03))" },
							children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tr", { children: visibleCols(cols).map(({ id }) => /* @__PURE__ */ (0, react_jsx_runtime.jsxs)("th", {
								style: {
									...th,
									cursor: "pointer"
								},
								onClick: () => toggleSort(id),
								children: [t(COL_LABEL[id]), sortCol === id ? ` ${sortDir === "asc" ? "↑" : "↓"}` : ""]
							}, id)) })
						}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tbody", { children: filtered.length === 0 && !loading ? /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tr", { children: /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
							style: {
								...td,
								textAlign: "center",
								color: "var(--color-muted-foreground)",
								padding: "40px 16px"
							},
							colSpan: visibleCols(cols).length,
							children: t("empty")
						}) }) : filtered.map((r) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("tr", { children: visibleCols(cols).map(({ id }) => /* @__PURE__ */ (0, react_jsx_runtime.jsx)("td", {
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
						}, id)) }, r.pageId)) })]
					}), /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
						style: {
							padding: "10px 16px",
							textAlign: "center",
							fontSize: 12,
							color: "var(--color-muted-foreground)"
						},
						children: loading ? t("loading") : t("count", { n: filtered.length })
					})]
				}),
				showExport && /* @__PURE__ */ (0, react_jsx_runtime.jsx)(ExportModal, {
					cols,
					labelFor: (id) => t(COL_LABEL[id]),
					fetchAll: async () => filtered,
					getCell: (r, id) => cell(r, id),
					filename: "page-analytics",
					sheetName: t("title"),
					total: filtered.length,
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

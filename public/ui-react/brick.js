(function(react, react_jsx_runtime) {
	//#region src/PageAnalyticsPage.tsx
	/**
	* Page Analytics tool page (MelisCmsPageAnalytics). First cut: loads the legacy tool in an iframe
	* via /melis/react-tool-page?key=<melisKey> — the same loading mechanism the old interface uses.
	*
	* The iframe is a <body> SINGLETON created once and only shown/hidden + repositioned (never
	* re-parented), so switching tabs doesn't reload the tool. NO sandbox: same-origin trusted Melis
	* content; a sandbox propagates to nested legacy iframes (modals, charts) and breaks them.
	*/
	var MELIS_KEY = "meliscms_page_analytics_display";
	var FRAME_ID = "melis-brick-frame-pageanalytics";
	function getFrame() {
		let f = document.getElementById(FRAME_ID);
		if (!f) {
			f = document.createElement("iframe");
			f.id = FRAME_ID;
			f.src = `/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`;
			f.title = "Page Analytics";
			f.style.cssText = "position:fixed;border:0;display:none;z-index:1;";
			document.body.appendChild(f);
		}
		return f;
	}
	function PageAnalyticsPage() {
		const anchorRef = (0, react.useRef)(null);
		(0, react.useEffect)(() => {
			const f = getFrame();
			const anchor = anchorRef.current;
			const sync = () => {
				const r = anchor.getBoundingClientRect();
				f.style.left = `${r.left}px`;
				f.style.top = `${r.top}px`;
				f.style.width = `${r.width}px`;
				f.style.height = `${r.height}px`;
				f.style.display = "block";
			};
			sync();
			const ro = new ResizeObserver(sync);
			ro.observe(anchor);
			window.addEventListener("resize", sync);
			window.addEventListener("scroll", sync, true);
			return () => {
				f.style.display = "none";
				ro.disconnect();
				window.removeEventListener("resize", sync);
				window.removeEventListener("scroll", sync, true);
			};
		}, []);
		return /* @__PURE__ */ (0, react_jsx_runtime.jsx)("div", {
			ref: anchorRef,
			style: {
				height: "100%",
				width: "100%",
				minHeight: 0
			}
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

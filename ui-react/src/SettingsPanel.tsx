import { useEffect, useState, type CSSProperties, type FormEvent } from 'react'
import {
  fetchAnalyticsSettings, fetchAnalyticsSites, saveAnalyticsSettings,
  type AnalyticsSettings, type SettingsField, type SiteOption,
} from './page-analytics-api'
import { useT, card, inputCss, btnPrimary } from './ui'
import { FormErrorBanner, collectIssues, okNotify, koNotify } from './shared/melis-form-errors'
import { useIsNarrow } from './shared/useIsNarrow'

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

const label: CSSProperties = { display: 'block', fontSize: 13, fontWeight: 600, marginBottom: 6 }
const help: CSSProperties = { fontSize: 12, color: 'var(--color-muted-foreground)', margin: '6px 0 0' }
const errCss: CSSProperties = { fontSize: 12, color: 'var(--color-destructive,#dc2626)', margin: '6px 0 0' }

export default function SettingsPanel() {
  const t = useT()
  const narrow = useIsNarrow()
  const [sites, setSites] = useState<SiteOption[]>([])
  const [site, setSite] = useState(0)
  const [state, setState] = useState<AnalyticsSettings | null>(null)
  const [moduleKey, setModuleKey] = useState('')
  const [values, setValues] = useState<Record<string, string>>({})
  const [files, setFiles] = useState<Record<string, File>>({})
  const [js, setJs] = useState('')
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})
  // Message d'en-tête de la bannière d'erreur (au sommet du formulaire) ; les champs fautifs sont
  // listés dessous via `errors`. `flash` ne sert plus qu'au succès (message vert près du bouton).
  const [formError, setFormError] = useState<string | null>(null)
  const [flash, setFlash] = useState<{ ok: boolean; msg: string } | null>(null)

  useEffect(() => { fetchAnalyticsSites().then((r) => setSites(r.sites)).catch(() => null) }, [])

  // Chargement de l'état d'un site. `key` force le schéma d'un AUTRE module que celui affecté
  // (l'utilisateur vient de changer le sélecteur) — sinon on prend celui du site.
  const load = (siteId: number, key?: string) => {
    if (!siteId) { setState(null); return }
    fetchAnalyticsSettings(siteId, key)
      .then((s) => {
        setState(s)
        setModuleKey(s.selectedKey)
        setValues(Object.fromEntries(Object.entries(s.values ?? {}).map(([k, v]) => [k, String(v ?? '')])))
        setJs(s.jsAnalytics)
        setFiles({})
        setErrors({})
        setFormError(null)
      })
      .catch(() => setState(null))
  }

  useEffect(() => { load(site) /* eslint-disable-line react-hooks/exhaustive-deps */ }, [site])

  const onModule = (key: string) => {
    setModuleKey(key)
    setErrors({})
    setFormError(null)
    // Recharge le schéma + les valeurs STOCKÉES pour ce module (le legacy fait de même via
    // getSettingsForm quand on change le sélecteur).
    if (site && key) load(site, key)
  }

  const selected = state?.modules.find((m) => m.key === moduleKey)
  const fields: SettingsField[] = state?.fields ?? []
  // Le JS brut n'a de sens que pour un module qui apporte ses propres réglages (c.-à-d. un
  // tracker externe type Google Analytics) — même règle que le JS legacy, mais dérivée de la
  // config plutôt que codée en dur sur la clé google.
  const showJs = !!selected?.settings

  async function submit(e: FormEvent) {
    e.preventDefault()
    if (!site || !moduleKey) return
    setSaving(true)
    setErrors({})
    setFormError(null)
    setFlash(null)

    const fd = new FormData()
    fd.append('pad_site_id', String(site))
    fd.append('pad_analytics_key', moduleKey)
    for (const f of fields) {
      if (f.type === 'file') continue
      fd.append(f.name, values[f.name] ?? '')
    }
    for (const [name, file] of Object.entries(files)) fd.append(name, file)
    fd.append('pads_js_analytics', showJs ? js : '')
    // Le legacy ne réécrit la clé privée que si le fichier a changé — même contrat ici.
    fd.append('fileChanged', Object.keys(files).length > 0 ? 'true' : 'false')

    try {
      const r = await saveAnalyticsSettings(fd)
      if (r.success) {
        setFlash({ ok: true, msg: r.textMessage })
        okNotify(t('set_save'), r.textMessage)
        load(site, moduleKey)
      } else {
        // `errors` legacy : { champ: { validateur: message, label?: … } } → 1 message par champ.
        const flat: Record<string, string> = {}
        for (const [name, msgs] of Object.entries(r.errors ?? {})) {
          const first = Object.entries(msgs ?? {}).find(([k]) => k !== 'label')
          if (first) flat[name] = String(first[1])
        }
        setErrors(flat)
        setFormError(r.textMessage || t('set_check_fields'))
        koNotify(t('set_error'), r.textMessage || '')
      }
    } catch {
      setFormError(t('set_error'))
      koNotify(t('set_error'))
    } finally {
      setSaving(false)
    }
  }

  return (
    <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: 20, padding: narrow ? 16 : 24, boxSizing: 'border-box', maxWidth: 760 }}>
      <div style={{ ...card, display: 'flex', flexDirection: 'column', gap: narrow ? 16 : 18, padding: narrow ? 14 : 20 }}>
        {/* Bannière d'erreur unifiée : au sommet du formulaire, énonce le problème ET liste chaque
            champ invalide (les champs restent aussi surlignés en rouge sous chacun via errCss). */}
        {(formError || Object.keys(errors).length > 0) && (
          <FormErrorBanner
            title={formError ?? t('set_check_fields')}
            issues={collectIssues(errors, {
              pad_analytics_key: t('set_module'),
              pads_js_analytics: t('set_js'),
              ...Object.fromEntries(fields.map((f) => [f.name, f.label])),
            })}
          />
        )}
        {/* Site */}
        <div>
          <label style={label} htmlFor="mcpa-site">{t('set_site')}</label>
          <select id="mcpa-site" style={inputCss} value={site} onChange={(e) => setSite(Number(e.target.value))}>
            <option value={0}>{t('set_site_ph')}</option>
            {sites.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
          </select>
        </div>

        {!site && <p style={help}>{t('set_pick_site')}</p>}

        {/* `site > 0` et non `site &&` : `site` est un NOMBRE — avec 0 le court-circuit renvoie 0,
            que React rend littéralement comme un « 0 » parasite sous le sélecteur. */}
        {site > 0 && state && (
          <>
            {/* Module analytics */}
            <div>
              <label style={label} htmlFor="mcpa-module">{t('set_module')}</label>
              <select id="mcpa-module" style={inputCss} value={moduleKey} onChange={(e) => onModule(e.target.value)}>
                <option value="">{t('set_module_ph')}</option>
                {state.modules.map((m) => <option key={m.key} value={m.key}>{m.label}</option>)}
              </select>
              <p style={help}>{t('set_module_help')}</p>
              {errors.pad_analytics_key && <p style={errCss}>{errors.pad_analytics_key}</p>}
            </div>

            {/* Réglages propres au module (schéma renvoyé par l'API) */}
            {fields.map((f) => (
              <div key={f.name}>
                <label style={label} htmlFor={`mcpa-${f.name}`}>{f.label}{f.required ? ' *' : ''}</label>
                {f.type === 'textarea' ? (
                  <textarea id={`mcpa-${f.name}`} style={{ ...inputCss, height: 100, padding: 10, fontFamily: 'inherit' }}
                    value={values[f.name] ?? ''} onChange={(e) => setValues((v) => ({ ...v, [f.name]: e.target.value }))} />
                ) : f.type === 'select' ? (
                  <select id={`mcpa-${f.name}`} style={inputCss} value={values[f.name] ?? ''}
                    onChange={(e) => setValues((v) => ({ ...v, [f.name]: e.target.value }))}>
                    <option value="" />
                    {f.options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                  </select>
                ) : f.type === 'file' ? (
                  <>
                    <input id={`mcpa-${f.name}`} type="file" style={{ ...inputCss, height: 'auto', padding: 8 }}
                      onChange={(e) => {
                        const file = e.target.files?.[0]
                        setFiles((prev) => { const next = { ...prev }; if (file) next[f.name] = file; else delete next[f.name]; return next })
                      }} />
                    {values[f.name] && <p style={help}>{t('set_current_file', { n: values[f.name] })}</p>}
                  </>
                ) : (
                  <input id={`mcpa-${f.name}`} type={f.type === 'password' ? 'password' : 'text'} style={inputCss}
                    value={values[f.name] ?? ''} onChange={(e) => setValues((v) => ({ ...v, [f.name]: e.target.value }))} />
                )}
                {f.tooltip && <p style={help}>{f.tooltip}</p>}
                {errors[f.name] && <p style={errCss}>{errors[f.name]}</p>}
              </div>
            ))}

            {moduleKey && moduleKey !== 'melis_cms_no_analytics' && fields.length === 0 && (
              <p style={help}>{t('set_no_settings')}</p>
            )}

            {/* Script JS personnalisé — injecté dans le <head> du front → admin plateforme uniquement */}
            {showJs && (
              <div>
                <label style={label} htmlFor="mcpa-js">{t('set_js')}</label>
                <textarea id="mcpa-js" style={{ ...inputCss, height: 160, padding: 10, fontFamily: 'ui-monospace,SFMono-Regular,Menlo,monospace', fontSize: 13, lineHeight: 1.5 }}
                  spellCheck={false} value={js} disabled={!state.jsEditable}
                  onChange={(e) => setJs(e.target.value)} />
                <p style={help}>{state.jsEditable ? t('set_js_help') : t('set_js_admin')}</p>
                {errors.pads_js_analytics && <p style={errCss}>{errors.pads_js_analytics}</p>}
              </div>
            )}

            {/* Étroit : le bouton prend la ligne, le message de succès passe dessous. */}
            <div style={{ display: 'flex', alignItems: narrow ? 'stretch' : 'center', flexDirection: narrow ? 'column' : 'row', gap: 12 }}>
              <button type="submit" style={{ ...btnPrimary, ...(narrow ? { justifyContent: 'center' } : {}), opacity: saving || !moduleKey ? 0.6 : 1 }} disabled={saving || !moduleKey}>
                {saving ? t('set_saving') : t('set_save')}
              </button>
              {/* Succès en VERT : --color-primary est le rouge Melis, un succès s'y lirait comme une erreur. */}
              {flash && (
                <span style={{ fontSize: 13, color: flash.ok ? 'var(--color-success,#16a34a)' : 'var(--color-destructive,#dc2626)' }}>
                  {flash.msg}
                </span>
              )}
            </div>
          </>
        )}
      </div>
    </form>
  )
}

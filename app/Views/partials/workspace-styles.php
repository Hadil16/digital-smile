<?php /** app/Views/partials/workspace-styles.php — styles communs des espaces (admin + employé), 100 % tokens. Inclus par admin-sidebar.php et employee-sidebar.php (donc uniquement sur les pages d'espace). */ ?>
<style>
    /* ============================================================
       Coquille d'espace premium — 100 % tokens (clair/sombre).
       Partagée par l'admin et l'employé.
       ============================================================ */
    .nav, .foot, .intro { display: none !important; }   /* on masque le chrome public */
    body { background: var(--color-bg); }

    .adm { font-family: 'Poppins', system-ui, sans-serif; color: var(--color-text); }

    /* --- Barre latérale fixe --- */
    .adm__side {
        position: fixed; top: 0; left: 0; width: 210px; height: 100vh;
        display: flex; flex-direction: column; padding: 22px 16px; gap: 8px;
        background: var(--color-surface-alt); border-right: 1px solid var(--color-border);
    }
    .adm__brand { display: flex; align-items: center; gap: 10px; padding: 4px 8px 18px; }
    .adm__brand img { height: 30px; border-radius: 7px; }
    .adm__brand span { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 17px; color: var(--color-text); }
    .adm__nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .adm__link {
        display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 12px;
        font-size: 14px; font-weight: 500; color: var(--color-muted); transition: background var(--transition), color var(--transition);
    }
    .adm__link:hover { background: var(--color-border); color: var(--color-text); }
    .adm__link.is-active { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); color: #fff; font-weight: 600; }
    .adm__link-ico { width: 20px; text-align: center; }
    .adm__link--logout { margin-top: auto; color: var(--color-danger); }
    .adm__link--logout:hover { background: rgba(179, 38, 30, .12); color: var(--color-danger); }

    /* --- Contenu principal + entête --- */
    .adm__main { margin-left: 210px; padding: 24px 26px 48px; min-height: 100vh; }
    .adm__top { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 0 0 26px; }
    .adm__greet { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 26px; color: var(--color-text); margin: 0; }
    .adm__subtitle { font-size: 13px; color: var(--color-muted); margin: 4px 0 0; }
    .adm__top-right { display: flex; align-items: center; gap: 14px; }
    .adm__bell { position: relative; display: inline-flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 999px; font-size: 19px; background: var(--color-surface-alt); border: 1px solid var(--color-border); }
    .adm__bell-badge { position: absolute; top: -3px; right: -3px; min-width: 18px; height: 18px; box-sizing: border-box;
        padding: 0 5px; border-radius: 999px; background: #8BC63F; color: #1f3d07; font-size: 11px; font-weight: 700; line-height: 18px; text-align: center; }
    .adm__avatar { display: inline-grid; place-items: center; width: 42px; height: 42px; border-radius: 999px;
        font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 15px; color: #1a1730;
        background: linear-gradient(135deg, #8BC63F, #6BA02C); }

    /* --- Titres de section + messages --- */
    .adm-section { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700;
        font-size: 18px; color: var(--color-text); margin: 30px 0 14px; }
    .adm-section:first-of-type { margin-top: 4px; }
    .adm-flash { background: var(--tint-accent); color: var(--color-accent-dark); border: 1px solid rgba(139, 198, 63, .4);
        border-radius: 12px; padding: 12px 16px; margin: 0 0 20px; font-size: 14px; }
    .adm-error { background: rgba(179, 38, 30, .12); color: var(--color-danger);
        border-radius: 12px; padding: 12px 16px; margin: 0 0 16px; font-size: 14px; }
    .adm-empty { background: var(--color-surface-alt); border: 1px dashed var(--color-border); color: var(--color-muted);
        border-radius: 14px; padding: 34px; text-align: center; font-size: 15px; }

    /* --- Cartes KPI --- */
    .adm__kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin: 0 0 22px; }
    .adm-kpi { border: 1px solid var(--color-border); border-radius: 16px; padding: 18px;
        transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition); }
    .adm-kpi:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: rgba(139, 198, 63, .5); }
    .adm-kpi--violet { background: rgba(74, 63, 158, .12); }
    .adm-kpi--amber  { background: rgba(240, 165, 0, .14); }
    .adm-kpi--green  { background: rgba(139, 198, 63, .16); }
    .adm-kpi--red    { background: rgba(179, 38, 30, .12); }
    .adm-kpi__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .adm-kpi__label { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--color-muted); }
    .adm-kpi__ico { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
        font-size: 17px; background: var(--color-surface); border: 1px solid var(--color-border); }
    .adm-kpi__num { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 28px;
        line-height: 1; color: var(--color-text); margin: 14px 0 0; }
    .adm-kpi__num--sm { font-size: 20px; word-break: break-word; }   /* valeurs longues (montants) */
    .adm-kpi__cap { font-size: 12px; color: var(--color-muted); margin: 6px 0 0; }

    /* --- Cartes (graphiques + panneaux) --- */
    .adm-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; padding: 20px; }
    .adm-card + .adm-card { margin-top: 16px; }
    .adm-card__title { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; font-size: 15px; color: var(--color-text); margin: 0 0 14px; }
    .adm__charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin: 0 0 22px; }
    .adm-card__canvas { position: relative; height: 260px; }

    /* --- Cartes "commande" (demandes / à facturer) --- */
    .adm-order { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px;
        padding: 20px 22px; margin: 0 0 14px; }
    .adm-order__head { display: flex; align-items: center; gap: 12px; margin: 0 0 12px; }
    .adm-order__code { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 700; color: var(--color-primary-light); }
    .adm-order__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px 20px; margin: 0 0 16px; font-size: 14px; }
    .adm-order__meta div { color: var(--color-text); }
    .adm-order__meta span { display: block; color: var(--color-muted); font-size: 12px; }
    .adm-order__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; border-top: 1px solid var(--color-border); padding-top: 16px; }
    .adm-order__actions form { display: flex; align-items: center; gap: 8px; margin: 0; }

    /* --- Boutons --- */
    .adm-btn { display: inline-block; border: 0; cursor: pointer; text-decoration: none; font-weight: 600;
        font-size: 14px; padding: 10px 20px; border-radius: 999px; font-family: inherit;
        transition: background var(--transition), color var(--transition), border-color var(--transition), filter var(--transition); }
    .adm-btn--primary { background: var(--color-accent); color: #1f3d07; }
    .adm-btn--primary:hover { background: var(--color-accent-dark); color: #fff; }
    .adm-btn--assign { background: linear-gradient(135deg, #4A3F9E, #6b5fd4); color: #fff; }
    .adm-btn--assign:hover { filter: brightness(1.08); }
    .adm-btn--danger { background: rgba(179, 38, 30, .12); color: var(--color-danger); }
    .adm-btn--danger:hover { background: rgba(179, 38, 30, .2); }
    .adm-btn--ghost { border: 1px solid var(--color-border); color: var(--color-text); padding: 8px 16px; }
    .adm-btn--ghost:hover { border-color: var(--color-primary-light); color: var(--color-primary-light); }

    /* --- Champs de formulaire --- */
    .adm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0 20px; }
    .adm-field { margin: 0 0 18px; }
    .adm-label { display: block; font-weight: 600; font-size: 14px; color: var(--color-text); margin: 0 0 6px; }
    .adm-input, .adm-select {
        border: 1px solid var(--color-border); font-size: 14px; font-family: inherit;
        background: var(--color-surface); color: var(--color-text); }
    .adm-input { width: 100%; box-sizing: border-box; padding: 12px 14px; border-radius: 12px; }
    .adm-select { padding: 9px 14px; border-radius: 999px; }
    .adm-input:focus, .adm-select:focus { outline: 2px solid var(--color-primary-light); outline-offset: 1px; border-color: var(--color-primary-light); }
    .adm-textarea { width: 100%; box-sizing: border-box; padding: 12px 14px; border-radius: 12px; min-height: 110px; resize: vertical;
        border: 1px solid var(--color-border); font-size: 14px; font-family: inherit; background: var(--color-surface); color: var(--color-text); }
    .adm-textarea:focus { outline: 2px solid var(--color-primary-light); outline-offset: 1px; border-color: var(--color-primary-light); }

    /* --- Tableaux --- */
    .adm-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .adm-table th { text-align: left; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--color-muted); padding: 0 12px 12px; white-space: nowrap; }
    .adm-table td { padding: 12px; border-top: 1px solid var(--color-border); color: var(--color-text); }
    .adm-table tbody tr { transition: background var(--transition); }
    .adm-table tbody tr:hover { background: var(--color-surface-alt); }
    .adm-table__num { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 600; color: var(--color-accent-dark); white-space: nowrap; }
    .adm-table__right { text-align: right; white-space: nowrap; }
    .adm-table__scroll { overflow-x: auto; }
    .adm-pill { display: inline-block; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .adm-pill--violet { background: rgba(74, 63, 158, .16); color: var(--color-primary-light); }
    .adm-pill--amber  { background: rgba(240, 165, 0, .16); color: #b8860b; }
    .adm-pill--green  { background: rgba(139, 198, 63, .18); color: var(--color-success); }
    .adm-pill--red    { background: rgba(179, 38, 30, .16); color: var(--color-danger); }
    .adm-pill--muted  { background: var(--color-surface-alt); color: var(--color-muted); }

    /* --- En-tête "dernières commandes" (tableau de bord admin) --- */
    .adm__recent-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
    .adm__all { font-family: 'Poppins', system-ui, sans-serif; font-weight: 600; font-size: 13px; color: #8BC63F; }
    .adm__empty { color: var(--color-muted); font-size: 14px; padding: 20px 12px; }

    /* --- Document facture (détail admin) --- */
    .adm-doc__bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin: 0 0 20px; }
    .adm-doc { background: var(--color-surface); border: 1px solid var(--color-border); border-top: 5px solid var(--color-primary);
        border-radius: 16px; padding: 34px; max-width: 720px; }
    .adm-doc__head { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;
        border-bottom: 1px solid var(--color-border); padding-bottom: 20px; margin: 0 0 20px; }
    .adm-doc__brand { font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-weight: 800; font-size: 22px; color: var(--color-primary-light); }
    .adm-doc__brand small { display: block; font-family: 'Poppins', system-ui, sans-serif; font-weight: 500; font-size: 12px; color: var(--color-muted); margin-top: 2px; }
    .adm-doc__num { text-align: right; }
    .adm-doc__num strong { display: block; font-family: 'Baloo 2', 'Poppins', system-ui, sans-serif; font-size: 18px; color: var(--color-text); }
    .adm-doc__num span { font-size: 13px; color: var(--color-muted); }
    .adm-doc__cols { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 0 0 24px; font-size: 14px; }
    .adm-doc__cols h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--color-muted); margin: 0 0 6px; }
    .adm-doc__cols p { margin: 0; color: var(--color-text); line-height: 1.5; }
    .adm-amounts { border: 1px solid var(--color-border); border-radius: 12px; overflow: hidden; max-width: 720px; }
    .adm-amounts div { display: flex; justify-content: space-between; padding: 12px 18px; font-size: 14px; border-bottom: 1px solid var(--color-border); }
    .adm-amounts div:last-child { border-bottom: 0; }
    .adm-amounts__ttc { background: var(--color-surface-alt); font-weight: 700; font-size: 16px; color: var(--color-primary-light); }

    /* Étiquette réservée aux lecteurs d'écran (accessibilité). */
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    /* --- Responsive : la sidebar devient une barre supérieure < 900px --- */
    @media (max-width: 900px) {
        .adm__side { position: static; width: auto; height: auto; flex-direction: row; align-items: center;
            gap: 6px; padding: 12px 14px; overflow-x: auto; border-right: 0; border-bottom: 1px solid var(--color-border); }
        .adm__brand { padding: 0 12px 0 4px; }
        .adm__brand span { display: none; }
        .adm__nav { flex-direction: row; flex: 0 0 auto; }
        .adm__link { white-space: nowrap; }
        .adm__link--logout { margin-top: 0; }
        .adm__main { margin-left: 0; padding: 20px 16px 40px; }
    }
</style>

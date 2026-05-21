<?php /* Shared styles - auto-included by all barangay pages */ ?>
<style>

/* Ã¢ââ¬Ã¢ââ¬ WHITE BACKGROUND THEME Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

:root {

    --white:      #ffffff;

    --surface:    #ffffff;      /* changed: was #f4f6f9, now white */

    --surface-2:  #f1f4f8;      /* changed: was #eef0f4, slightly deeper for contrast */

    --border:     #e2e8f0;

    --border-2:   #cbd5e1;

    --muted:      #64748b;

    --text:       #1e293b;

    --navy:       #0f172a;

    --navy-mid:   #1e3a5f;

    --navy-light: #eef2f7;

    --red:        #dc2626;

    --red-l:      #fef2f2;

    --green:      #16a34a;

    --green-l:    #f0fdf4;

    --amber:      #d97706;

    --amber-l:    #fffbeb;

}



* { margin: 0; padding: 0; box-sizing: border-box; }

body { font-family: 'DM Sans', sans-serif; background: var(--white); display: flex; width: 100%; min-height: 100vh; color: var(--text); font-size: 14px; line-height: 1.5; }



/* Ã¢ââ¬Ã¢ââ¬ SIDEBAR Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.sidebar {

    width: 232px; background: var(--white); min-height: 100vh;

    display: flex; flex-direction: column; position: fixed;

    top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100;

}

.sidebar-logo {

    padding: 24px 20px 20px; border-bottom: 1px solid var(--border);

}

.sidebar-logo h1 {

    color: var(--navy); font-size: 16px; font-weight: 700;

    letter-spacing: 0.02em; text-transform: uppercase;

}

.sidebar-logo p { color: var(--muted); font-size: 11px; margin-top: 2px; font-weight: 400; }

.sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; display: flex; flex-direction: column; }

.sidebar-menu::-webkit-scrollbar { width: 0; }

.sidebar-menu .menu-section { display: block; padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }


.menu-item {

    display: flex; align-items: center; gap: 10px; padding: 8px 20px;

    color: var(--muted); font-size: 13px; font-weight: 400;

    transition: all 0.15s; cursor: pointer; background: none;

    border: none; width: 100%; text-align: left; font-family: 'DM Sans', sans-serif;

    text-decoration: none; white-space: nowrap; overflow: hidden;

}

.menu-item:hover { color: var(--text); background: var(--surface-2); }

.menu-item.active { color: var(--navy); font-weight: 500; background: var(--navy-light); }

.menu-item.active .menu-dot { background: var(--navy); }

.menu-dot {

    width: 5px; height: 5px; border-radius: 50%;

    background: var(--border); flex-shrink: 0; transition: background 0.15s;

}

.menu-item:hover .menu-dot { background: var(--text); }

.menu-badge {

    margin-left: auto; background: var(--surface-2); color: var(--muted);

    font-size: 11px; font-weight: 500; padding: 1px 7px;

    border-radius: 20px; font-family: 'DM Mono', monospace;

}

.menu-badge.alert { background: var(--red-l); color: var(--red); }

.sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }

.sidebar-footer a {

    display: flex; align-items: center; gap: 10px; color: var(--muted);

    text-decoration: none; font-size: 13px; transition: color 0.15s;

}

.sidebar-footer a:hover { color: var(--text); }



/* Ã¢ââ¬Ã¢ââ¬ MAIN Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.main { margin-left: 232px; flex: 1; width: calc(100% - 232px); display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }

.topbar {

    background: var(--white); padding: 0 28px; height: 56px;

    display: flex; align-items: center; justify-content: space-between;

    border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10;

}

.topbar-title { color: var(--navy); font-size: 14px; font-weight: 500; }

.topbar-date { color: var(--muted); font-size: 12px; }

.topbar-right { display: flex; align-items: center; gap: 12px; }

.brgy-chip {

    background: var(--navy-light); color: var(--navy-mid);

    font-size: 11px; font-weight: 500; padding: 4px 10px;

    border-radius: 20px; border: 1px solid var(--border);

}

.avatar-btn {

    width: 32px; height: 32px; background: var(--navy);

    border-radius: 50%; display: flex; align-items: center; justify-content: center;

    color: #fff; font-size: 12px; font-weight: 500;

    cursor: pointer; border: none; font-family: 'DM Sans', sans-serif;

    transition: opacity 0.15s;

}

.avatar-btn:hover { opacity: 0.85; }



/* Ã¢ââ¬Ã¢ââ¬ CONTENT Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.content { padding: 24px 28px; }

.section { display: none; }

.section.active { display: block; }

.page-header { margin-bottom: 20px; }

.page-header h2 { font-size: 18px; font-weight: 500; color: var(--navy); }

.page-header p { color: var(--muted); font-size: 13px; margin-top: 2px; }



/* Ã¢ââ¬Ã¢ââ¬ STAT CARDS Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }

.stat-card {

    background: var(--white); border-radius: 8px; padding: 18px 20px;

    border: 1px solid var(--border);

}

.stat-card.clickable { cursor: pointer; transition: border-color 0.15s; }

.stat-card.clickable:hover { border-color: var(--navy); }

.stat-num { font-size: 28px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; line-height: 1; }

.stat-label { color: var(--muted); font-size: 12px; margin-top: 6px; }

.stat-hint { color: var(--navy-mid); font-size: 11px; margin-top: 2px; opacity: 0.6; }

.stat-card.danger { border-color: var(--red-l); }

.stat-card.danger .stat-num { color: var(--red); }



/* Ã¢ââ¬Ã¢ââ¬ QUICK ACTIONS Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 24px; }

.action-btn {

    background: var(--white); border: 1px solid var(--border);

    border-radius: 8px; padding: 14px 16px; text-align: left;

    cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif;

    width: 100%;

}

.action-btn:hover { border-color: var(--navy); background: var(--navy-light); }

.action-btn h3 { font-size: 13px; font-weight: 500; color: var(--navy); }

.action-btn p { font-size: 12px; color: var(--muted); margin-top: 2px; }



/* Ã¢ââ¬Ã¢ââ¬ CARDS Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.card { background: var(--white); border-radius: 8px; border: 1px solid var(--border); padding: 20px; margin-bottom: 14px; }

.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }

.card-title { font-size: 13px; font-weight: 500; color: var(--navy); }

.card-sub { font-size: 12px; color: var(--muted); margin-top: 1px; }

.card-empty { text-align: center; padding: 28px; color: var(--muted); font-size: 13px; }



/* Ã¢ââ¬Ã¢ââ¬ SECTION LABEL Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.sec-label {

    font-size: 10px; font-weight: 600; letter-spacing: 0.1em;

    text-transform: uppercase; color: var(--muted); margin-bottom: 10px;

}



/* Ã¢ââ¬Ã¢ââ¬ TABLE Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }

.data-table th {

    text-align: left; padding: 8px 12px; color: var(--muted);

    font-size: 11px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase;

    border-bottom: 1px solid var(--border); background: var(--surface-2);

}

.data-table td { padding: 10px 12px; border-bottom: 1px solid var(--border); color: var(--text); }

.data-table tr:last-child td { border-bottom: none; }

.data-table tr:hover td { background: var(--surface-2); }

.data-table .mono { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--navy); }



/* Ã¢ââ¬Ã¢ââ¬ TAGS / BADGES Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.tag {

    display: inline-block; padding: 2px 8px; border-radius: 20px;

    font-size: 11px; font-weight: 500;

}

.tag-navy    { background: var(--navy-light); color: var(--navy-mid); }

.tag-active  { background: var(--green-l); color: var(--green); }

.tag-inactive{ background: var(--red-l); color: var(--red); }

.tag-high    { background: var(--red-l); color: var(--red); }

.tag-medium  { background: var(--amber-l); color: var(--amber); }

.tag-low     { background: var(--green-l); color: var(--green); }

.tag-resolved{ background: var(--surface-2); color: var(--muted); }



/* Ã¢ââ¬Ã¢ââ¬ BUTTONS Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.btn {

    padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500;

    cursor: pointer; border: 1px solid var(--border); transition: all 0.15s;

    font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 6px;

}

.btn-primary { background: var(--navy); color: #fff; border-color: var(--navy); }

.btn-primary:hover { background: var(--navy-mid); border-color: var(--navy-mid); }

.btn-secondary { background: var(--white); color: var(--text); }

.btn-secondary:hover { background: var(--surface-2); }

.btn-danger { background: var(--red-l); color: var(--red); border-color: transparent; }

.btn-danger:hover { background: #fee2e2; }

.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; font-size: 12px; padding: 6px 12px; }

.btn-ghost:hover { background: var(--surface-2); color: var(--text); }

.btn-sm { padding: 5px 12px; font-size: 12px; }



/* Ã¢ââ¬Ã¢ââ¬ SEARCH / FILTER Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.search-bar {

    display: flex; gap: 10px; align-items: center;

    margin-bottom: 14px; flex-wrap: wrap;

}

.search-input {

    flex: 1; min-width: 200px; padding: 8px 12px;

    border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); background: var(--white);

    font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.15s;

}

.search-input:focus { border-color: var(--navy); }

.search-input::placeholder { color: var(--muted); }

.filter-select {

    padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); background: var(--white);

    font-family: 'DM Sans', sans-serif; outline: none; cursor: pointer;

}



/* Ã¢ââ¬Ã¢ââ¬ MODAL Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.modal-backdrop { pointer-events: none; display: none; position: fixed; inset: 0;

    background: rgba(15,23,42,0.35); z-index: 500;

    align-items: center; justify-content: center;

}

.modal-backdrop.open { pointer-events: auto; display: flex; }

.modal {

    background: var(--white); border-radius: 10px; width: 90%; max-width: 560px;

    max-height: 88vh; display: flex; flex-direction: column;

    border: 1px solid var(--border); overflow: hidden;

    animation: slideUp 0.2s ease;

}

.modal.modal-wide { max-width: 760px; }

@keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-header {

    padding: 18px 22px; border-bottom: 1px solid var(--border);

    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;

}

.modal-header h3 { color: var(--navy); font-size: 14px; font-weight: 500; }

.modal-close {

    background: none; border: none; cursor: pointer; color: var(--muted);

    font-size: 18px; line-height: 1; padding: 2px 6px; border-radius: 4px;

    transition: background 0.15s;

}

.modal-close:hover { background: var(--surface-2); }

.modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }



/* Ã¢ââ¬Ã¢ââ¬ FORM Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.form-group { display: flex; flex-direction: column; gap: 5px; }

.form-group.full { grid-column: 1 / -1; }

.form-group label { color: var(--text); font-size: 12px; font-weight: 500; }

.form-group input, .form-group select, .form-group textarea {

    padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); outline: none;

    font-family: 'DM Sans', sans-serif; transition: border-color 0.15s; background: var(--white);

}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--navy); }

.form-group .hint { color: var(--muted); font-size: 11px; }

.form-actions {

    display: flex; gap: 8px; justify-content: flex-end;

    margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);

}

.req { color: var(--red); }



/* Ã¢ââ¬Ã¢ââ¬ ALERT BANNER Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.alert-banner {

    padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;

    font-size: 13px; display: flex; align-items: center; gap: 8px;

    border: 1px solid transparent;

}

.alert-banner.success { background: var(--green-l); color: var(--green); border-color: #bbf7d0; }

.alert-banner.error   { background: var(--red-l); color: var(--red); border-color: #fecaca; }



/* Ã¢ââ¬Ã¢ââ¬ INFO ROW Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.info-row {

    display: flex; justify-content: space-between; align-items: center;

    padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 13px;

}

.info-row:last-child { border-bottom: none; }

.info-label { color: var(--muted); }

.info-value { color: var(--text); font-weight: 500; }



/* Ã¢ââ¬Ã¢ââ¬ OFFICIAL CARD Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.official-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }

.official-row:last-child { border-bottom: none; }

.official-avatar {

    width: 80px; height: 80px; border-radius: 50%; background: var(--navy-light);

    color: var(--navy-mid); display: flex; align-items: center; justify-content: center;

    font-size: 60px; font-weight: 500; flex-shrink: 0;

}

.official-name { font-size: 15px; font-weight: 500; color: var(--text); }

.official-pos  { font-size: 13px; color: var(--muted); }



/* Ã¢ââ¬Ã¢ââ¬ ZONE CARD Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.zones-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }

.zone-card {

    background: var(--surface-2); border: 1px solid var(--border);

    border-radius: 8px; padding: 14px 16px; cursor: pointer;

    transition: border-color 0.15s;

}

.zone-card:hover { border-color: var(--navy); background: var(--navy-light); }

.zone-num { font-size: 20px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; }

.zone-leader { font-size: 13px; font-weight: 500; color: var(--text); margin-top: 4px; }

.zone-meta { font-size: 11px; color: var(--muted); margin-top: 2px; }



/* Ã¢ââ¬Ã¢ââ¬ ALERT ITEM Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.alert-row {

    display: flex; align-items: flex-start; gap: 12px;

    padding: 12px 0; border-bottom: 1px solid var(--border);

}

.alert-row:last-child { border-bottom: none; }

.alert-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; background: var(--muted); }

.alert-dot.high   { background: var(--red); }

.alert-dot.medium { background: var(--amber); }

.alert-msg  { font-size: 13px; color: var(--text); flex: 1; }

.alert-time { font-size: 11px; color: var(--muted); margin-top: 2px; }



/* Ã¢ââ¬Ã¢ââ¬ PROFILE HERO Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.profile-hero {

    background: var(--navy); border-radius: 8px; padding: 24px;

    color: #fff; display: flex; align-items: center; gap: 18px; margin-bottom: 16px;

}

.profile-avatar-lg {

    width: 52px; height: 52px; border-radius: 8px;

    background: rgba(255,255,255,0.15); display: flex; align-items: center;

    justify-content: center; font-size: 20px; font-weight: 300; flex-shrink: 0;

}

.profile-hero-name { font-size: 18px; font-weight: 500; }

.profile-hero-sub { font-size: 13px; opacity: 0.65; margin-top: 2px; }



/* Ã¢ââ¬Ã¢ââ¬ PROFILE FIELDS GRID Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.field-box { background: var(--surface-2); border-radius: 6px; padding: 12px 14px; border: 1px solid var(--border); }

.field-box.full { grid-column: 1 / -1; }

.field-label { font-size: 11px; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }

.field-val { font-size: 13px; font-weight: 500; color: var(--text); }



/* Ã¢ââ¬Ã¢ââ¬ REPORT TAB Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.tab-row { display: flex; gap: 4px; margin-bottom: 14px; }

.tab-btn {

    padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;

    cursor: pointer; border: 1px solid var(--border); background: var(--white);

    color: var(--muted); transition: all 0.15s; font-family: 'DM Sans', sans-serif;

}

.tab-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }

.tab-panel { display: none; }

.tab-panel.active { display: block; }



/* Ã¢ââ¬Ã¢ââ¬ QR HERO Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.qr-hero {

    background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px;

    padding: 36px; text-align: center; margin-bottom: 14px;

}

.qr-hero h3 { font-size: 15px; font-weight: 500; color: var(--navy); }

.qr-hero p { font-size: 13px; color: var(--muted); margin-top: 4px; }



/* Ã¢ââ¬Ã¢ââ¬ CHECKBOX Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

.checkbox-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; }

.checkbox-row input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--navy); cursor: pointer; }

.checkbox-row label { font-size: 13px; color: var(--text); cursor: pointer; }



/* Ã¢ââ¬Ã¢ââ¬ SEARCH SUMMARY Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬Ã¢ââ¬ */

#search-summary { font-size: 12px; color: var(--muted); margin-bottom: 8px; }


/* Page transition */
body { opacity: 0; animation: fadeIn 0.15s ease forwards; }
@keyframes fadeIn { to { opacity: 1; } }
</style>
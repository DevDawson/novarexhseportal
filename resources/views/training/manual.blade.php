<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PortalHSE — System Training Manual</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;font-size:10pt;color:#1a1a2e;background:#f8f9fa;line-height:1.6}
  a{color:#1d4ed8;text-decoration:none}
  /* Layout */
  .wrapper{max-width:960px;margin:0 auto;background:#fff;box-shadow:0 0 20px rgba(0,0,0,.1)}
  /* Cover */
  .cover{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1d4ed8 100%);color:#fff;padding:80px 60px;min-height:500px;display:flex;flex-direction:column;justify-content:center}
  .cover-logo{font-size:13pt;letter-spacing:3px;text-transform:uppercase;opacity:.7;margin-bottom:20px}
  .cover-title{font-size:32pt;font-weight:800;line-height:1.2;margin-bottom:16px}
  .cover-sub{font-size:13pt;opacity:.85;margin-bottom:40px;max-width:500px}
  .cover-meta{display:flex;gap:30px;flex-wrap:wrap}
  .cover-meta span{font-size:9pt;opacity:.7;border-left:2px solid rgba(255,255,255,.3);padding-left:10px}
  /* TOC */
  .toc{padding:40px 60px;border-bottom:1px solid #e5e7eb}
  .toc h2{font-size:16pt;color:#0f172a;margin-bottom:20px;padding-bottom:8px;border-bottom:3px solid #1d4ed8}
  .toc-grid{columns:2;column-gap:40px}
  .toc-item{display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px dotted #d1d5db;font-size:9pt;break-inside:avoid}
  .toc-item a{color:#374151}
  .toc-item .pg{color:#6b7280;font-weight:600;min-width:30px;text-align:right}
  .toc-section{font-weight:700;color:#1d4ed8;margin-top:10px;font-size:10pt}
  /* Sections */
  .section{padding:40px 60px;border-bottom:2px solid #e5e7eb}
  .section-header{background:linear-gradient(90deg,#1d4ed8,#3b82f6);color:#fff;padding:14px 20px;border-radius:8px;margin-bottom:24px;display:flex;align-items:center;gap:12px}
  .section-num{background:rgba(255,255,255,.2);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12pt;flex-shrink:0}
  .section-title{font-size:15pt;font-weight:700}
  .section-desc{font-size:9pt;opacity:.9;margin-top:2px}
  h3{font-size:11pt;color:#1e3a5f;margin:20px 0 8px;padding-left:10px;border-left:3px solid #3b82f6}
  h4{font-size:10pt;color:#374151;margin:14px 0 6px;font-weight:700}
  p{margin-bottom:10px;font-size:9.5pt}
  /* Role Badges */
  .roles{display:flex;flex-wrap:wrap;gap:6px;margin:8px 0 12px}
  .role{padding:2px 10px;border-radius:20px;font-size:8pt;font-weight:600}
  .role-md{background:#fef3c7;color:#92400e}
  .role-hse{background:#d1fae5;color:#065f46}
  .role-hr{background:#ede9fe;color:#4c1d95}
  .role-acc{background:#fee2e2;color:#991b1b}
  .role-bd{background:#dbeafe;color:#1e40af}
  .role-esg{background:#fce7f3;color:#9d174d}
  .role-all{background:#f3f4f6;color:#374151}
  .role-admin{background:#1f2937;color:#f9fafb}
  /* Info boxes */
  .info{background:#eff6ff;border-left:4px solid #3b82f6;padding:10px 14px;margin:10px 0;border-radius:0 6px 6px 0;font-size:9pt}
  .warn{background:#fffbeb;border-left:4px solid #f59e0b;padding:10px 14px;margin:10px 0;border-radius:0 6px 6px 0;font-size:9pt}
  .tip{background:#f0fdf4;border-left:4px solid #22c55e;padding:10px 14px;margin:10px 0;border-radius:0 6px 6px 0;font-size:9pt}
  .danger{background:#fff1f2;border-left:4px solid #ef4444;padding:10px 14px;margin:10px 0;border-radius:0 6px 6px 0;font-size:9pt}
  /* Tables */
  table{width:100%;border-collapse:collapse;font-size:9pt;margin:12px 0}
  th{background:#1d4ed8;color:#fff;padding:7px 10px;text-align:left;font-weight:600}
  td{padding:6px 10px;border-bottom:1px solid #e5e7eb;vertical-align:top}
  tr:nth-child(even) td{background:#f8faff}
  tr:last-child td{border-bottom:2px solid #1d4ed8}
  /* Steps */
  .steps{counter-reset:step;margin:12px 0}
  .step{display:flex;gap:14px;margin-bottom:10px;align-items:flex-start}
  .step-num{background:#1d4ed8;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:9pt;font-weight:700;flex-shrink:0;margin-top:1px}
  .step-body{flex:1;font-size:9.5pt}
  /* Workflow */
  .workflow{display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin:12px 0;padding:14px;background:#f8faff;border-radius:8px;border:1px solid #dbeafe}
  .wf-box{background:#1d4ed8;color:#fff;padding:5px 14px;border-radius:4px;font-size:8.5pt;font-weight:600;white-space:nowrap}
  .wf-box.gray{background:#6b7280}
  .wf-box.green{background:#15803d}
  .wf-box.orange{background:#d97706}
  .wf-box.red{background:#dc2626}
  .wf-arrow{color:#6b7280;font-size:14pt;line-height:1}
  /* Field list */
  .fields{display:grid;grid-template-columns:repeat(2,1fr);gap:6px;margin:10px 0}
  .field{background:#f8faff;border:1px solid #dbeafe;padding:6px 10px;border-radius:4px;font-size:8.5pt}
  .field strong{color:#1e3a5f;display:block;font-size:8pt}
  /* Print */
  @media print{
    body{background:#fff}
    .wrapper{box-shadow:none;max-width:100%}
    .section{page-break-inside:avoid}
    .no-print{display:none}
    @page{margin:20mm 15mm}
  }
  /* Nav bar */
  .navbar{background:#0f172a;color:#fff;padding:12px 60px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100}
  .navbar span{font-size:9pt;opacity:.7}
  .navbar a{color:#60a5fa;font-size:9pt}
  /* Risk matrix colors */
  .risk-low{background:#dcfce7;color:#166534;padding:2px 8px;border-radius:3px;font-weight:600;font-size:8pt}
  .risk-med{background:#fef9c3;color:#713f12;padding:2px 8px;border-radius:3px;font-weight:600;font-size:8pt}
  .risk-high{background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:3px;font-weight:600;font-size:8pt}
  .risk-crit{background:#500724;color:#fff;padding:2px 8px;border-radius:3px;font-weight:600;font-size:8pt}
  /* divider */
  .divider{height:2px;background:linear-gradient(90deg,#1d4ed8,transparent);margin:20px 0}
  ul,ol{padding-left:20px;font-size:9.5pt;margin-bottom:10px}
  li{margin-bottom:4px}
</style>
</head>
<body>

<div class="navbar no-print">
  <span>PortalHSE — Training Manual</span>
  <a href="#" onclick="window.print()">🖨 Print / Save as PDF</a>
</div>

<div class="wrapper">

<!-- ═══════════════════ COVER ═══════════════════ -->
<div class="cover">
  <div class="cover-logo">PortalHSE · HSE &amp; Sustainability Management System</div>
  <div class="cover-title">System Training Manual</div>
  <div class="cover-sub">Complete step-by-step guide for all roles — covering every module, workflow, and feature of the PortalHSE platform.</div>
  <div class="cover-meta">
    <span>Version 2.0 — June 2026</span>
    <span>11 Modules · All Roles Covered</span>
    <span>Confidential — Internal Use Only</span>
  </div>
</div>

<!-- ═══════════════════ TABLE OF CONTENTS ═══════════════════ -->
<div class="toc" id="toc">
  <h2>Table of Contents</h2>
  <div class="toc-grid">
    <div class="toc-item toc-section"><span>Getting Started</span></div>
    <div class="toc-item"><a href="#s0">System Overview &amp; Login</a><span class="pg">§1</span></div>
    <div class="toc-item"><a href="#dashboard">Dashboard &amp; Navigation</a><span class="pg">§2</span></div>
    <div class="toc-item"><a href="#roles">Roles &amp; Permissions</a><span class="pg">§3</span></div>
    <div class="toc-item toc-section"><span>Core HSE Modules</span></div>
    <div class="toc-item"><a href="#m1">Module 1 — HSE System</a><span class="pg">§4</span></div>
    <div class="toc-item"><a href="#m2">Module 2 — Incident Investigation</a><span class="pg">§5</span></div>
    <div class="toc-item"><a href="#m3">Module 3 — Risk Assessment (HAZID)</a><span class="pg">§6</span></div>
    <div class="toc-item"><a href="#m4">Module 4 — Risk Assessment (HAZOP)</a><span class="pg">§7</span></div>
    <div class="toc-item"><a href="#m5">Module 5 — HIRA</a><span class="pg">§8</span></div>
    <div class="toc-item"><a href="#m6">Module 6 — Permit to Work (PTW)</a><span class="pg">§9</span></div>
    <div class="toc-item toc-section"><span>Environmental &amp; ESG Modules</span></div>
    <div class="toc-item"><a href="#m7">Module 7 — Environmental Management (EMS)</a><span class="pg">§10</span></div>
    <div class="toc-item"><a href="#m8">Module 8 — ESG Management</a><span class="pg">§11</span></div>
    <div class="toc-item"><a href="#m9">Module 9 — EIA/ESIA (12 Steps)</a><span class="pg">§12</span></div>
    <div class="toc-item"><a href="#m10">Module 10 — Environmental Audit</a><span class="pg">§13</span></div>
    <div class="toc-item"><a href="#m11">Module 11 — Audit Management System</a><span class="pg">§14</span></div>
    <div class="toc-item toc-section"><span>Supporting Modules</span></div>
    <div class="toc-item"><a href="#m12">Module 12 — Training &amp; Competency</a><span class="pg">§15</span></div>
    <div class="toc-item"><a href="#m13">Module 13 — HR &amp; Payroll</a><span class="pg">§16</span></div>
    <div class="toc-item"><a href="#m14">Module 14 — Finance &amp; Expenses</a><span class="pg">§17</span></div>
    <div class="toc-item"><a href="#m15">Module 15 — Document Control</a><span class="pg">§18</span></div>
    <div class="toc-item"><a href="#m16">Module 16 — Energy Management (EnMS)</a><span class="pg">§19</span></div>
    <div class="toc-item"><a href="#admin">System Administration</a><span class="pg">§20</span></div>
  </div>
</div>

<!-- ═══════════════════ §1 OVERVIEW ═══════════════════ -->
<div class="section" id="s0">
  <div class="section-header">
    <div class="section-num">1</div>
    <div><div class="section-title">System Overview &amp; Logging In</div>
    <div class="section-desc">What PortalHSE is and how to access it</div></div>
  </div>

  <p>PortalHSE is an integrated HSE &amp; Sustainability management platform built on Laravel and Filament. It covers Health, Safety &amp; Environment (HSE), Environmental Management, ESG reporting, EIA/ESIA compliance, permit-to-work authorization, HR/payroll, and finance — all in one role-protected system.</p>

  <h3>Accessing the System</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Open a web browser and navigate to your organisation's PortalHSE URL (e.g. <strong>https://yourdomain.com/admin</strong>).</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Enter your <strong>Email Address</strong> and <strong>Password</strong> on the login page and click <strong>Sign in</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">On first login, you may be prompted to verify your email or change your password — follow the on-screen instructions.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">You are taken to the <strong>Dashboard</strong>. The sidebar on the left shows only the modules your role has access to.</div></div>
  </div>

  <div class="info"><strong>Note:</strong> If you cannot see a module in the sidebar, your account does not have the required permission. Contact your System Administrator.</div>

  <h3>Logging Out</h3>
  <p>Click your <strong>user avatar</strong> (top right corner) → select <strong>Sign out</strong>. Always log out on shared computers.</p>

  <h3>Password Reset</h3>
  <p>On the login page click <strong>Forgot your password?</strong> → enter your registered email → check your inbox for a reset link. Links expire in 60 minutes.</p>
</div>

<!-- ═══════════════════ §2 DASHBOARD ═══════════════════ -->
<div class="section" id="dashboard">
  <div class="section-header">
    <div class="section-num">2</div>
    <div><div class="section-title">Dashboard &amp; Navigation</div>
    <div class="section-desc">Understanding the home screen and how to move through the system</div></div>
  </div>

  <h3>Dashboard Widgets</h3>
  <p>The Dashboard is the first screen after login. It displays KPI widgets relevant to your role. Each widget auto-refreshes every 30 seconds.</p>

  <table>
    <tr><th>Widget</th><th>What it shows</th><th>Who sees it</th></tr>
    <tr><td>Stats Overview</td><td>Total projects, staff, active permits, open incidents</td><td>MD, HSE, HR, Accountant, BD</td></tr>
    <tr><td>HSE KPI Overview</td><td>Incident frequency rate, open CAPA, compliance %</td><td>MD, HSE Staff, HR Director</td></tr>
    <tr><td>Revenue vs Expenses</td><td>Monthly bar chart of invoiced revenue vs outflows</td><td>MD, Accountant, BD</td></tr>
    <tr><td>Incident Trend Chart</td><td>Rolling 12-month incident count by type</td><td>MD, HSE Staff, HR Director</td></tr>
    <tr><td>Incident Severity Chart</td><td>Pie breakdown of incidents by severity</td><td>MD, HSE Staff, HR Director</td></tr>
    <tr><td>HAZID KPI Overview</td><td>Total hazards, open/controlled/critical counts</td><td>MD, HSE Staff</td></tr>
    <tr><td>HAZOP KPI Overview</td><td>Studies, open nodes, risk distribution</td><td>MD, HSE Staff</td></tr>
    <tr><td>PTW KPI Overview</td><td>Active permits, overdue, compliance rate</td><td>MD, HSE Staff</td></tr>
    <tr><td>Open CAPA Actions</td><td>List of overdue and upcoming CAPA actions</td><td>MD, HSE Staff</td></tr>
    <tr><td>High-Risk Hazards</td><td>Hazards with residual risk score ≥ 13</td><td>MD, HSE Staff</td></tr>
    <tr><td>EMS KPI Overview</td><td>Environmental aspects, permits expiring, monitoring due</td><td>MD, HSE Staff, BD</td></tr>
    <tr><td>ESG KPI Overview</td><td>Open grievances, targets on-track, engagement count</td><td>MD, ESG Officer, BD</td></tr>
    <tr><td>EIA/ESIA Overview</td><td>Active ESIA projects by step/status</td><td>MD, HSE Staff, BD</td></tr>
    <tr><td>Open Audit Findings</td><td>Unresolved findings from internal audits</td><td>MD, HSE Staff, BD</td></tr>
    <tr><td>Expiring Documents</td><td>Corporate documents expiring in next 30 days</td><td>Document managers</td></tr>
    <tr><td>Expiring Permits</td><td>PTW permits expiring within 48 hours</td><td>MD, HSE Staff</td></tr>
    <tr><td>Expiring Licences</td><td>Environmental permits/licences due for renewal</td><td>MD, HSE Staff</td></tr>
  </table>

  <h3>Sidebar Navigation</h3>
  <p>The left sidebar is divided into 11 primary HSE modules (listed in order) followed by supporting modules. Click any group heading to expand/collapse it. Click an item to open that resource.</p>

  <div class="info"><strong>Tip:</strong> You can collapse the sidebar by clicking the hamburger icon at the top of the sidebar to gain more screen space, especially useful on smaller monitors.</div>

  <h3>Common Actions on Every List Page</h3>
  <ul>
    <li><strong>New [Record]</strong> button (top right) — creates a new entry</li>
    <li><strong>Search bar</strong> — full-text search across key fields</li>
    <li><strong>Filter</strong> icon — apply date ranges, status filters, category filters</li>
    <li><strong>Columns</strong> icon — toggle which columns are visible</li>
    <li><strong>Export</strong> (where available) — download CSV or PDF</li>
    <li><strong>Row actions</strong> — click the ⋮ menu on any row to Edit, View, Delete, or export a PDF</li>
  </ul>

  <h3>Common Actions on Every Edit/Create Page</h3>
  <ul>
    <li>Fields marked with <strong>*</strong> are required</li>
    <li>Click <strong>Save changes</strong> (bottom or top right) to save</li>
    <li>Click <strong>Cancel</strong> to discard changes and return to the list</li>
    <li>Tabs across the top of the edit page open related sub-sections (RelationManagers)</li>
  </ul>
</div>

<!-- ═══════════════════ §3 ROLES ═══════════════════ -->
<div class="section" id="roles">
  <div class="section-header">
    <div class="section-num">3</div>
    <div><div class="section-title">Roles &amp; Permissions</div>
    <div class="section-desc">What each user role can see and do</div></div>
  </div>

  <p>Every user is assigned one or more roles by the System Administrator. Roles control which sidebar items are visible and which actions (create, edit, delete) are available.</p>

  <table>
    <tr><th>Role</th><th>Primary Responsibilities</th><th>Key Access</th></tr>
    <tr><td><span class="role role-md">MD</span> Managing Director</td><td>Full system oversight and approvals</td><td>All modules — read, create, edit, approve, delete</td></tr>
    <tr><td><span class="role role-hse">HSE Manager</span></td><td>Owns all HSE, environmental, audit systems</td><td>Full access to all HSE/EMS/ESIA/Audit modules + training</td></tr>
    <tr><td><span class="role role-hse">HSE Staff</span></td><td>Day-to-day HSE operations</td><td>Incidents, risks, HIRA, HAZOP, PTW, EMS, audits, CAPA, training</td></tr>
    <tr><td><span class="role role-hr">HR Director</span></td><td>Staff and training management</td><td>Staff, Payroll, Leave, Training, Certifications, Competency</td></tr>
    <tr><td><span class="role role-bd">Business Director</span></td><td>Projects, tenders, ESG reporting</td><td>Projects, Tenders, Deliverables, Hazards (view), Audits (view), ESG Targets</td></tr>
    <tr><td><span class="role role-acc">Accountant</span></td><td>Finance and payroll administration</td><td>Invoices, Field Expenses, Petty Cash, Payroll, Chart of Accounts, Reports</td></tr>
    <tr><td><span class="role role-esg">ESG Officer</span></td><td>Sustainability and social reporting</td><td>Stakeholders, Grievances, Social Indicators, Governance, Ethics, ESG Targets, Documents</td></tr>
    <tr><td><span class="role role-all">Supervisor / Line Manager</span></td><td>On-site safety management</td><td>Incidents, PTW (limited), Risks, CAPA, Leave (team), Field Expenses</td></tr>
    <tr><td><span class="role role-all">Employee</span></td><td>Self-service HR and incident reporting</td><td>Submit Incidents, Leave Requests, Field Expenses, view Dashboard</td></tr>
    <tr><td><span class="role role-all">Contractor</span></td><td>Site operations</td><td>Submit Incidents only</td></tr>
    <tr><td><span class="role role-all">Lead Auditor</span></td><td>Audit execution and NC management</td><td>Internal Audits, CAPA, Incidents (view)</td></tr>
    <tr><td><span class="role role-admin">System Admin</span></td><td>User and system configuration</td><td>Users, Departments, Company Settings, Roles</td></tr>
  </table>

  <div class="warn"><strong>Important:</strong> Never share login credentials. Each person must have their own account. Role assignments are logged and auditable.</div>
</div>

<!-- ═══════════════════ MODULE 1: HSE SYSTEM ═══════════════════ -->
<div class="section" id="m1">
  <div class="section-header">
    <div class="section-num">4</div>
    <div><div class="section-title">Module 1 — HSE System</div>
    <div class="section-desc">Projects · CAPA · Lessons Learned</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-hse">HSE Manager</span>
    <span class="role role-bd">Business Director</span>
    <span class="role role-acc">Accountant</span>
    <span class="role role-hr">HR Director</span>
  </div>

  <h3>1.1 Projects</h3>
  <p>Projects are the central record in the system. Almost every other module (Incidents, Risks, ESIA, Field Expenses, Invoices) links back to a Project. Always create the Project before creating records in other modules.</p>

  <h4>Creating a New Project</h4>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">In the sidebar click <strong>HSE System → Projects</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Click <strong>New Project</strong> (top right).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Fill in the required fields: <strong>Project Title</strong>, <strong>Client</strong>, <strong>Project Manager</strong>, <strong>Start Date</strong>, <strong>Status</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Click <strong>Save</strong>. The project now appears in all project dropdowns across the system.</div></div>
  </div>
  <p>On the project <strong>Edit</strong> page, tabs give access to linked: <em>Incidents</em>, <em>Risks</em>, <em>Deliverables</em>, <em>ESIA Audits</em>, <em>Field Expenses</em>, and <em>Invoices</em>.</p>

  <h3>1.2 CAPA — Corrective &amp; Preventive Actions</h3>
  <p>CAPA is the central action-tracking register. Any finding from an incident, audit, inspection, or risk review can generate a CAPA action to ensure it is resolved.</p>

  <div class="workflow">
    <div class="wf-box gray">Draft</div><div class="wf-arrow">→</div>
    <div class="wf-box">Open</div><div class="wf-arrow">→</div>
    <div class="wf-box orange">In Progress</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Completed</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Verified &amp; Closed</div>
  </div>

  <h4>Creating a CAPA Action</h4>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>HSE System → CAPA</strong> and click <strong>New CAPA</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select the <strong>CAPA Type</strong>: Corrective (fixes a problem that occurred) or Preventive (stops a problem occurring).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Select the <strong>Source Type</strong> — Incident, Audit, Inspection, Risk Assessment, or Compliance Finding — and link to the specific record.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Enter <strong>Title</strong>, <strong>Category</strong> (Safety, Environmental, Quality, Process, Compliance), <strong>Description</strong>, and <strong>Root Cause</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Assign to a <strong>Responsible Person</strong> and set a <strong>Due Date</strong>. Click <strong>Save</strong>.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">When the action is done, the assignee opens the CAPA, fills in <strong>Action Taken</strong>, attaches <strong>Evidence</strong>, and changes status to <strong>Completed</strong>.</div></div>
    <div class="step"><div class="step-num">7</div><div class="step-body">The HSE Manager or MD verifies the closure and sets status to <strong>Verified &amp; Closed</strong>.</div></div>
  </div>

  <div class="warn"><strong>Overdue CAPA:</strong> The Dashboard widget highlights overdue CAPAs in red. Responsible persons should receive notifications automatically.</div>

  <h3>1.3 Lessons Learned</h3>
  <p>Record lessons from incidents, near-misses, audits, or project close-outs so knowledge is shared across the organisation.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>HSE System → Lessons Learned</strong> and click <strong>New Lesson Learned</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select <strong>Category</strong> and link to a <strong>Project</strong> (optional) or <strong>Incident</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Describe the <strong>Event / Situation</strong>, <strong>What Was Learned</strong>, and <strong>Recommended Actions</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Save. Lessons are searchable by all HSE staff to prevent recurrence.</div></div>
  </div>
</div>

<!-- ═══════════════════ MODULE 2: INCIDENTS ═══════════════════ -->
<div class="section" id="m2">
  <div class="section-header">
    <div class="section-num">5</div>
    <div><div class="section-title">Module 2 — Incident Investigation &amp; Reporting</div>
    <div class="section-desc">Report, investigate, and close HSE incidents</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Supervisor</span>
    <span class="role role-all">Employee</span>
    <span class="role role-all">Contractor</span>
  </div>

  <h3>Incident Types</h3>
  <table>
    <tr><th>Type</th><th>Description</th></tr>
    <tr><td>Near Miss</td><td>An event that could have caused harm but did not</td></tr>
    <tr><td>Unsafe Act</td><td>A behaviour that deviates from safe work procedures</td></tr>
    <tr><td>Unsafe Condition</td><td>A physical or environmental hazard that could cause harm</td></tr>
    <tr><td>First Aid</td><td>Injury requiring first-aid treatment only</td></tr>
    <tr><td>Medical Treatment</td><td>Injury requiring medical treatment beyond first aid</td></tr>
    <tr><td>Lost Time Incident</td><td>Injury causing at least one lost workday</td></tr>
    <tr><td>Fatality</td><td>Death resulting from a work incident</td></tr>
    <tr><td>Environmental</td><td>Spill, release, or environmental damage</td></tr>
    <tr><td>Property Damage</td><td>Damage to equipment, vehicles, or facilities</td></tr>
  </table>

  <h3>Reporting an Incident (All Users)</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Incident Management → Investigation &amp; Reporting</strong> and click <strong>New Incident</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select the <strong>Project</strong>, enter <strong>Incident Date &amp; Time</strong>, <strong>Location</strong>, and <strong>Incident Type</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Rate <strong>Likelihood</strong> (1–5) and <strong>Impact</strong> (1–5). The system calculates the <strong>Risk Score</strong> automatically.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Write a clear <strong>Description</strong> of what happened — include who was involved, what they were doing, and what the consequences were.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Click <strong>Save</strong>. The incident is created with status <em>Reported</em>.</div></div>
  </div>

  <div class="danger"><strong>Regulatory Notice:</strong> Fatalities and Lost Time Incidents must also be reported to OSHA/relevant authority within 24 hours by the HSE Manager. The system records do not replace statutory notification.</div>

  <h3>Investigating an Incident (HSE Staff / HSE Manager)</h3>
  <p>Open the incident record and click <strong>Edit</strong>. Scroll to the <strong>Investigation Method</strong> section.</p>

  <h4>5 Whys Analysis</h4>
  <p>Select <strong>5 Whys</strong> as the method. Answer each "Why" question in sequence:</p>
  <ul>
    <li><strong>Why 1:</strong> Why did the incident happen?</li>
    <li><strong>Why 2:</strong> Why did <em>that</em> happen?</li>
    <li><strong>Why 3:</strong> Continue drilling down...</li>
    <li><strong>Why 4 &amp; 5:</strong> Until you reach the root cause</li>
    <li>Enter the final <strong>Root Cause</strong> in the dedicated field</li>
  </ul>

  <h4>Fishbone / Ishikawa Analysis</h4>
  <p>Select <strong>Fishbone</strong> as the method. Fill in causes across 6 categories:</p>
  <div class="fields">
    <div class="field"><strong>People Causes</strong>Training gaps, fatigue, behaviour</div>
    <div class="field"><strong>Equipment Causes</strong>Tool failure, maintenance, design flaw</div>
    <div class="field"><strong>Method Causes</strong>Procedure deficiency, wrong technique</div>
    <div class="field"><strong>Materials Causes</strong>Wrong material, contamination</div>
    <div class="field"><strong>Environment Causes</strong>Weather, lighting, noise, layout</div>
    <div class="field"><strong>Management Causes</strong>Supervision failure, communication</div>
  </div>

  <h4>TapRooT Analysis</h4>
  <p>For high-severity incidents. Fill in: <strong>Timeline of Events</strong>, <strong>Witnesses</strong>, <strong>Direct Causes</strong>, <strong>Contributing Factors</strong>, and <strong>Verification Review</strong>.</p>

  <h4>Barrier Analysis</h4>
  <p>Identify: <strong>Barriers that failed</strong>, <strong>Barriers that worked</strong>, and evaluate <strong>Prevention Effectiveness</strong>.</p>

  <h3>Closing an Incident</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">In the <strong>Corrective Actions</strong> section, list all actions taken, assign owners, and set due dates.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Set <strong>Investigation Status</strong> to <em>Completed</em>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Attach any supporting evidence (photos, witness statements) via the file upload.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">The HSE Manager or MD reviews and closes the incident.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">To download the incident report as PDF, click the <strong>PDF Report</strong> row action from the list page.</div></div>
  </div>

  <div class="info">The <strong>Investigation tab</strong> on the edit page gives access to linked sub-investigations for complex multi-factor incidents.</div>
</div>

<!-- ═══════════════════ MODULE 3: HAZID ═══════════════════ -->
<div class="section" id="m3">
  <div class="section-header">
    <div class="section-num">6</div>
    <div><div class="section-title">Module 3 — Risk Assessment (HAZID — Qualitative)</div>
    <div class="section-desc">Hazard Identification and qualitative risk register</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Supervisor</span>
  </div>

  <p>The Risk Register captures all identified risks using a qualitative approach — each risk is rated by Likelihood and Severity to produce a Risk Score (L × S, max 25).</p>

  <h3>Risk Matrix</h3>
  <table>
    <tr><th>Score</th><th>Level</th><th>Recommended Action</th></tr>
    <tr><td>1–5</td><td><span class="risk-low">LOW</span></td><td>Monitor; review at next scheduled assessment</td></tr>
    <tr><td>6–12</td><td><span class="risk-med">MEDIUM</span></td><td>Implement controls within 30 days; assign owner</td></tr>
    <tr><td>13–19</td><td><span class="risk-high">HIGH</span></td><td>Immediate action required; escalate to HSE Manager</td></tr>
    <tr><td>20–25</td><td><span class="risk-crit">CRITICAL</span></td><td>Stop work if required; MD and HSE Manager notified immediately</td></tr>
  </table>

  <h3>Creating a Risk Entry</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Risk Assessment (HAZID) → Risk Register</strong> and click <strong>New Risk</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select the <strong>Project</strong> and <strong>Category</strong> (Safety, Environmental, Financial, Operational, Legal, Reputational).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter a clear <strong>Risk Title</strong> and <strong>Description</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Rate <strong>Likelihood</strong> (1=Rare, 5=Almost Certain) and <strong>Severity</strong> (1=Insignificant, 5=Catastrophic). The <strong>Risk Score</strong> calculates live.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Enter <strong>Mitigation Measures</strong>, assign a <strong>Risk Owner</strong>, set <strong>Review Date</strong>, and select <strong>Status</strong> (Open / Mitigated / Closed).</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">Click <strong>Save</strong>.</div></div>
  </div>

  <div class="tip"><strong>Best practice:</strong> Review all Open and Mitigated risks quarterly. Update the Risk Score after controls are implemented to demonstrate risk reduction.</div>
</div>

<!-- ═══════════════════ MODULE 4: HAZOP ═══════════════════ -->
<div class="section" id="m4">
  <div class="section-header">
    <div class="section-num">7</div>
    <div><div class="section-title">Module 4 — Risk Assessment (HAZOP — Quantitative)</div>
    <div class="section-desc">Hazard and Operability Studies for process operations</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
  </div>

  <p>HAZOP is a structured method for identifying hazards in process and operational systems. It uses guide words (No, Less, More, Part Of, Reverse, Other Than) applied to process parameters to identify deviations.</p>

  <div class="workflow">
    <div class="wf-box gray">Draft</div><div class="wf-arrow">→</div>
    <div class="wf-box">In Progress</div><div class="wf-arrow">→</div>
    <div class="wf-box orange">Under Review</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Approved</div><div class="wf-arrow">→</div>
    <div class="wf-box gray">Closed</div>
  </div>

  <h3>Creating a HAZOP Study</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Risk Assessment (HAZOP) → HAZOP Studies</strong> and click <strong>New HAZOP Study</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">The system auto-generates a <strong>Study Reference</strong> number.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter <strong>Title</strong>, link to <strong>Project</strong> and <strong>Department</strong>, specify <strong>Facility Area</strong> and <strong>P&amp;ID Reference</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Define <strong>Scope</strong>, <strong>Objectives</strong>, and <strong>Methodology</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Assign the <strong>Facilitator</strong> and list <strong>Team Members</strong>.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">Save. Status is <em>Draft</em>.</div></div>
  </div>

  <h3>Adding HAZOP Nodes (Study Nodes Tab)</h3>
  <p>On the study edit page, click the <strong>Nodes</strong> tab to add individual process nodes:</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Click <strong>New Node</strong> within the Nodes tab.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">For each node, enter the <strong>Node Name</strong>, <strong>Design Intent</strong>, <strong>Guide Word</strong>, <strong>Deviation</strong>, <strong>Causes</strong>, <strong>Consequences</strong>, <strong>Safeguards</strong>, and <strong>Risk Score</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Set <strong>Recommendations</strong> and assign an action owner.</div></div>
  </div>

  <h3>Review and Approval</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Set study status to <em>Under Review</em> and assign a <strong>Reviewer</strong> with a <strong>Review Date</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Reviewer opens the study, checks all nodes, and adds review notes.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Assign <strong>Approved By</strong> (MD or HSE Manager) with <strong>Approval Date</strong> and <strong>Comments</strong>. Status moves to <em>Approved</em>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Download the HAZOP Study report as PDF via the row action <strong>Study PDF</strong>.</div></div>
  </div>
</div>

<!-- ═══════════════════ MODULE 5: HIRA ═══════════════════ -->
<div class="section" id="m5">
  <div class="section-header">
    <div class="section-num">8</div>
    <div><div class="section-title">Module 5 — HIRA (Hazard Identification &amp; Risk Assessment)</div>
    <div class="section-desc">Detailed hazard register with initial and residual risk tracking</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Supervisor</span>
  </div>

  <p>HIRA provides a detailed hazard register where each hazard has an <strong>Initial Risk</strong> (before controls) and a <strong>Residual Risk</strong> (after controls are applied). The goal is to reduce residual risk to an acceptable level.</p>

  <div class="workflow">
    <div class="wf-box gray">Draft</div><div class="wf-arrow">→</div>
    <div class="wf-box">Identified</div><div class="wf-arrow">→</div>
    <div class="wf-box orange">Controlled</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Closed</div>
  </div>

  <h3>Creating a Hazard Entry</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>HIRA → HAZID / HIRA</strong> and click <strong>New Hazard</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">The system auto-generates a <strong>Hazard ID</strong>. Select <strong>Project</strong>, <strong>Department</strong>, and enter <strong>Date Identified</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Describe the hazard: <strong>Activity/Task</strong>, <strong>Location</strong>, <strong>Hazard Category</strong>, <strong>Hazard Source</strong>, <strong>Potential Causes</strong>, <strong>Potential Consequences</strong>, and <strong>Who Might Be Harmed</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Initial Risk:</strong> Rate <strong>Initial Likelihood</strong> and <strong>Initial Severity</strong>. The Initial Risk Score calculates automatically.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Controls:</strong> Document <strong>Existing Controls</strong> already in place, and any <strong>Additional Controls</strong> needed.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body"><strong>Residual Risk:</strong> Rate <strong>Residual Likelihood</strong> and <strong>Residual Severity</strong> after controls are applied. The Residual Risk Score calculates automatically.</div></div>
    <div class="step"><div class="step-num">7</div><div class="step-body">Assign a <strong>Responsible Person</strong>, set <strong>Priority Level</strong> and <strong>Target Completion Date</strong>.</div></div>
    <div class="step"><div class="step-num">8</div><div class="step-body">Save. Status is <em>Draft</em>. Change to <em>Identified</em> when ready for review.</div></div>
  </div>

  <h3>Verification and Closure</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">In the <strong>Approval &amp; Verification</strong> section, set <strong>Approved By</strong> and <strong>Approval Date</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">After controls are implemented, set <strong>Verification Method</strong>, <strong>Verification Evidence</strong>, and <strong>Verified By</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">To close: enter <strong>Closure Comments</strong> and change status to <em>Closed</em>.</div></div>
  </div>

  <p>The <strong>Actions tab</strong> on the edit page allows you to create follow-up action items linked to this hazard. The <strong>Attachments tab</strong> lets you upload photos, inspection reports, or measurement data.</p>

  <div class="info">The Dashboard widget <strong>High-Risk Hazards</strong> always shows hazards with a residual risk score ≥ 13 so they are never forgotten.</div>

  <h4>Exporting HIRA Report</h4>
  <p>From the list page, click the ⋮ menu on a hazard row and select <strong>HIRA PDF Report</strong> to download a formatted risk assessment document.</p>
</div>

<!-- ═══════════════════ MODULE 6: PTW ═══════════════════ -->
<div class="section" id="m6">
  <div class="section-header">
    <div class="section-num">9</div>
    <div><div class="section-title">Module 6 — Permit to Work (PTW) System</div>
    <div class="section-desc">Issue, approve, and close work permits for hazardous activities</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Supervisor</span>
    <span class="role role-all">All Authenticated Users</span>
  </div>

  <p>A Permit to Work (PTW) is a formal documented system that authorises certain types of hazardous work. No hazardous work should commence without a valid, approved permit.</p>

  <h3>Permit Types</h3>
  <table>
    <tr><th>Permit Type</th><th>Typical Activities</th></tr>
    <tr><td>Hot Work</td><td>Welding, cutting, grinding, flame use near flammables</td></tr>
    <tr><td>Cold Work</td><td>Non-sparking maintenance and construction</td></tr>
    <tr><td>Confined Space</td><td>Entry into tanks, vessels, pits, sewers</td></tr>
    <tr><td>Electrical</td><td>Live electrical work, isolation and re-energisation</td></tr>
    <tr><td>Excavation</td><td>Digging, trenching, piling near underground services</td></tr>
    <tr><td>Working at Height</td><td>Any work above 1.8 m from ground level</td></tr>
    <tr><td>Radiation / Chemical</td><td>Handling hazardous substances</td></tr>
  </table>

  <div class="workflow">
    <div class="wf-box gray">Draft</div><div class="wf-arrow">→</div>
    <div class="wf-box">Submitted</div><div class="wf-arrow">→</div>
    <div class="wf-box orange">Approved</div><div class="wf-arrow">→</div>
    <div class="wf-box green">In Progress</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Completed</div><div class="wf-arrow">→</div>
    <div class="wf-box gray">Closed</div>
  </div>

  <h3>Creating a Permit (Requestor)</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Permit to Work (PTW) → Permit to Work</strong> and click <strong>New Permit</strong>. The system auto-generates a <strong>Permit Number</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select <strong>Permit Type</strong> and link to a <strong>Work Order</strong> (optional).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Fill in <strong>Location</strong>, <strong>Site Area</strong>, <strong>Department</strong>, <strong>Valid From</strong> / <strong>Valid To</strong> dates and times, and <strong>Duration Estimate</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Enter <strong>Personnel</strong>: Requested By, Area Authority, Supervisor, contractor name and number of workers.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Complete the <strong>Risk Assessment</strong> section: Likelihood, Severity, Risk Score (auto-calculated), Risk Classification. Link to a <strong>HIRA Hazard</strong> or <strong>HAZOP Node</strong> if applicable.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">List all <strong>Hazards Identified</strong>, <strong>Precautions</strong>, <strong>PPE Required</strong>, and <strong>Emergency Procedures</strong>.</div></div>
    <div class="step"><div class="step-num">7</div><div class="step-body">Complete the relevant <strong>Safety Controls</strong>: tick isolation required, LOTO verified, gas test required/results, fire watch, barricading, emergency standby as applicable.</div></div>
    <div class="step"><div class="step-num">8</div><div class="step-body">Save and change status to <em>Submitted</em>.</div></div>
  </div>

  <h3>Approving a Permit (HSE Manager / Area Authority)</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Open the submitted permit. Review all sections carefully.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Check the <strong>Approvals tab</strong> to see the approval chain progress.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">If satisfied, set the permit to <em>Approved</em> and enter your approval details. Work may then commence.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">If concerns exist, add comments and return to the requestor for amendment.</div></div>
  </div>

  <h3>During Work Execution</h3>
  <ul>
    <li>Use the <strong>Toolbox Talks tab</strong> to record pre-job safety briefings (date, attendees, topics covered)</li>
    <li>Use the <strong>Inspections tab</strong> to log any site safety inspections carried out during the work</li>
    <li>Use the <strong>Isolation Records tab</strong> if LOTO (Lockout/Tagout) is applied</li>
    <li>If work is suspended, change status to <em>Suspended</em> and enter the <strong>Suspension Reason</strong></li>
  </ul>

  <h3>Closing a Permit</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Once work is complete, open the permit and fill in <strong>Closeout Notes</strong> and <strong>Final Inspection Notes</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Set <strong>Completion Date</strong>, <strong>Closed By</strong>, and confirm with <strong>Completion Confirmed By</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Status changes to <em>Closed</em>. The permit is now archived.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Download the permit PDF via the <strong>PTW Permit PDF</strong> row action for physical records.</div></div>
  </div>

  <div class="danger"><strong>Critical:</strong> A permit expires at its <strong>Valid To</strong> time. If work is not completed, a new permit must be requested. Never extend a permit verbally.</div>
</div>

<!-- ═══════════════════ MODULE 7: EMS ═══════════════════ -->
<div class="section" id="m7">
  <div class="section-header">
    <div class="section-num">10</div>
    <div><div class="section-title">Module 7 — Environmental Management System (EMS)</div>
    <div class="section-desc">ISO 14001-aligned environmental management</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-esg">ESG Officer</span>
  </div>

  <p>The EMS module implements ISO 14001 requirements for managing environmental aspects, monitoring, legal compliance, permits, waste, and spills.</p>

  <h3>7.1 Environmental Aspects &amp; Impacts</h3>
  <p>An <strong>Environmental Aspect</strong> is an element of an activity that can interact with the environment. The associated <strong>Environmental Impact</strong> is the change to the environment resulting from the aspect.</p>

  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Environmental Management (EMS) → Aspects &amp; Impacts</strong> and click <strong>New Aspect</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select <strong>Project</strong>, enter <strong>Activity/Process</strong>, <strong>Impact Category</strong> (Air, Water, Land, Biodiversity, Energy, Waste, etc.).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Describe the <strong>Environmental Aspect</strong> (e.g., "Fuel combustion") and the <strong>Environmental Impact</strong> (e.g., "Greenhouse gas emissions").</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Rate <strong>Likelihood</strong> and <strong>Severity</strong> (1–5 each). The <strong>Significance Score</strong> = L × S calculates automatically.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Enter <strong>Existing Controls</strong> and <strong>Proposed Controls</strong>. Assign a <strong>Responsible Person</strong> and <strong>Target Completion Date</strong>.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">Set <strong>Status</strong>: Open, In Progress, or Controlled.</div></div>
  </div>

  <h3>7.2 Legal &amp; Compliance Register</h3>
  <p>Tracks all applicable environmental laws, regulations, and standards.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Legal &amp; Compliance Register</strong> and click <strong>New Legal Requirement</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Enter the <strong>Regulation Name</strong>, <strong>Issuing Authority</strong>, <strong>Applicability</strong> to your operations, and <strong>Compliance Obligations</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Set <strong>Review Date</strong> for when compliance should next be checked. Assign a <strong>Responsible Person</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Update <strong>Compliance Status</strong> (Compliant / Non-Compliant / Not Applicable) after each review.</div></div>
  </div>

  <h3>7.3 Environmental Monitoring Records</h3>
  <p>Records results from environmental monitoring activities (e.g., air quality, water discharge, noise levels).</p>
  <ul>
    <li>Select the <strong>Parameter</strong> monitored and the <strong>Monitoring Location</strong></li>
    <li>Enter <strong>Measured Value</strong>, <strong>Unit</strong>, <strong>Date of Measurement</strong>, and <strong>Method</strong></li>
    <li>Compare against <strong>Regulatory Limit</strong> — a flag indicates whether the result is within or outside limits</li>
    <li>Attach the lab report or field data sheet</li>
  </ul>

  <h3>7.4 Waste Tracking</h3>
  <p>Logs all waste generated, classified by type and disposal method.</p>
  <ul>
    <li>Enter <strong>Waste Type</strong> (Hazardous / Non-Hazardous), <strong>Description</strong>, <strong>Quantity</strong> and <strong>Unit</strong></li>
    <li>Record <strong>Generation Date</strong>, <strong>Disposal Method</strong>, and <strong>Licensed Contractor</strong> used</li>
    <li>Attach disposal manifests as evidence</li>
  </ul>

  <h3>7.5 Spill Reports</h3>
  <p>Documents any accidental release of hazardous materials to the environment.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Report as soon as possible after the spill is controlled. Go to <strong>Spill Reports → New Spill Report</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Enter <strong>Spill Date/Time</strong>, <strong>Location</strong>, <strong>Material Spilled</strong>, <strong>Estimated Volume</strong>, and <strong>Environmental Receptor Affected</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Document <strong>Immediate Response Actions</strong> taken and <strong>Cleanup Methods</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Attach photographs and lab results if available. Create a linked CAPA for follow-up actions.</div></div>
  </div>

  <h3>7.6 Environmental Permits</h3>
  <p>Tracks all environmental licences and permits (e.g., discharge permits, emissions licences, NEMA certificates).</p>
  <ul>
    <li>Enter <strong>Permit Name</strong>, <strong>Issuing Authority</strong>, <strong>Permit Number</strong>, <strong>Issue Date</strong>, and <strong>Expiry Date</strong></li>
    <li>The Dashboard <strong>Expiring Licences</strong> widget shows permits due for renewal in the next 30 days</li>
    <li>Attach the permit document for reference</li>
  </ul>
</div>

<!-- ═══════════════════ MODULE 8: ESG ═══════════════════ -->
<div class="section" id="m8">
  <div class="section-header">
    <div class="section-num">11</div>
    <div><div class="section-title">Module 8 — ESG Management</div>
    <div class="section-desc">Environmental, Social and Governance reporting</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-esg">ESG Officer</span>
    <span class="role role-bd">Business Director</span>
  </div>

  <h3>8.1 Stakeholder Register</h3>
  <p>Documents all stakeholders (communities, NGOs, regulators, investors) that have an interest in company activities.</p>
  <ul>
    <li>Enter <strong>Name</strong>, <strong>Organization</strong>, <strong>Category</strong> (Community / Government / NGO / Investor), <strong>Influence Level</strong>, and contact details</li>
    <li>Use the <strong>Stakeholder Engagements</strong> register to log every interaction</li>
  </ul>

  <h3>8.2 Grievance Management</h3>
  <p>Records and tracks complaints from external stakeholders or affected communities.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>ESG Management → Grievances</strong> and click <strong>New Grievance</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Enter <strong>Complainant Name</strong> (or Anonymous), <strong>Date Received</strong>, <strong>Category</strong>, and <strong>Description</strong> of the grievance.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Assign to a <strong>Responsible Person</strong> and set a <strong>Response Deadline</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">As the grievance is investigated and resolved, update <strong>Response Provided</strong> and <strong>Resolution Notes</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Change status to <em>Resolved</em> once the complainant has been notified. The Dashboard widget shows open grievances.</div></div>
  </div>

  <h3>8.3 Social Indicators</h3>
  <p>Tracks quantitative social performance metrics (e.g., local employment %, community investment, training hours).</p>

  <h3>8.4 Governance Policy Register</h3>
  <p>Maintains the company's governance policies (Anti-Bribery, Whistleblower, Code of Conduct, etc.). Each policy entry records the version, effective date, owner, and next review date.</p>

  <h3>8.5 Ethics Incidents</h3>
  <p>Confidentially records ethics violations, bribery attempts, or code-of-conduct breaches. Access is restricted to MD and ESG Officer.</p>

  <h3>8.6 ESG Targets</h3>
  <p>Sets and tracks measurable ESG performance targets (e.g., "Reduce GHG emissions by 20% by 2027").</p>
  <ul>
    <li>Enter <strong>Target Description</strong>, <strong>Indicator</strong>, <strong>Baseline Value</strong>, <strong>Target Value</strong>, <strong>Target Year</strong>, and <strong>Frequency</strong> of reporting</li>
    <li>Update <strong>Current Value</strong> at each reporting period</li>
    <li>The Dashboard <strong>ESG Targets Progress</strong> widget shows a progress bar for each target</li>
  </ul>
</div>

<!-- ═══════════════════ MODULE 9: EIA/ESIA ═══════════════════ -->
<div class="section" id="m9">
  <div class="section-header">
    <div class="section-num">12</div>
    <div><div class="section-title">Module 9 — EIA/ESIA (Environmental &amp; Social Impact Assessment)</div>
    <div class="section-desc">12-step structured process from project registration to compliance monitoring</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-esg">ESG Officer</span>
    <span class="role role-bd">Business Director</span>
  </div>

  <p>The EIA/ESIA module guides you through the complete 12-step assessment process required by environmental regulators (NEMC in Tanzania). Follow the steps in order — each builds on the previous.</p>

  <div class="info"><strong>Important:</strong> Always start by creating a Project (Module 1) and an ESIA Project Registration (Step 1). Every subsequent step links back to the same ESIA project.</div>

  <table>
    <tr><th>Step</th><th>Module Item</th><th>Purpose</th></tr>
    <tr><td>Step 1</td><td>Project Registration</td><td>Register the project requiring ESIA</td></tr>
    <tr><td>Step 2</td><td>Screening</td><td>Determine ESIA category (A/B/C) based on scale and sensitivity</td></tr>
    <tr><td>Step 3</td><td>Scoping</td><td>Define the scope and key issues to be assessed</td></tr>
    <tr><td>Step 4</td><td>Baseline Data</td><td>Document existing environmental and social conditions</td></tr>
    <tr><td>Step 5 &amp; 6</td><td>Impact Matrix</td><td>Assess all project impacts across activities and receptors</td></tr>
    <tr><td>Step 7</td><td>Alternatives Analysis</td><td>Compare project alternatives and justify preferred option</td></tr>
    <tr><td>Step 8</td><td>Mitigation (ESMP)</td><td>Environmental and Social Management Plan — mitigations and monitoring</td></tr>
    <tr><td>Step 9</td><td>Stakeholder Consultation</td><td>Record public participation and community engagement</td></tr>
    <tr><td>Step 10</td><td>ESIA Reports</td><td>Compile and manage the formal ESIA report documents</td></tr>
    <tr><td>Step 11</td><td>Regulatory Submissions</td><td>Track submissions to NEMC or other regulatory bodies</td></tr>
    <tr><td>Step 12</td><td>Compliance Monitoring</td><td>Ongoing monitoring of conditions of approval</td></tr>
  </table>

  <h3>Step 2 — Screening in Detail</h3>
  <p>Screening determines whether a full EIA is required and at what level:</p>
  <table>
    <tr><th>Screening Score (Scale × Sensitivity × Pollution)</th><th>Category</th><th>Required Study</th></tr>
    <tr><td>≤ 5</td><td>Category C</td><td>No EIA required — Environmental Project Brief only</td></tr>
    <tr><td>6–10</td><td>Category B</td><td>Limited EIA / Environmental Audit</td></tr>
    <tr><td>≥ 11</td><td>Category A</td><td>Full EIA/ESIA required</td></tr>
  </table>

  <h3>Step 5 &amp; 6 — Impact Assessment Matrix</h3>
  <p>For each impact entry, assess:</p>
  <ul>
    <li><strong>Activity</strong> — which project activity causes the impact</li>
    <li><strong>Receptor</strong> — what is affected (air, water, soil, community, biodiversity)</li>
    <li><strong>Phase</strong> — Construction, Operation, or Decommissioning</li>
    <li><strong>Impact Magnitude</strong> (1–5), <strong>Duration</strong>, <strong>Reversibility</strong>, <strong>Likelihood</strong></li>
    <li><strong>Sensitivity of Receptor</strong> (1–5) and <strong>Cumulative Impacts</strong></li>
    <li>System calculates <strong>Significance Rating</strong> automatically</li>
  </ul>

  <h3>Generating the ESIA Report PDF</h3>
  <p>From the <strong>ESIA Reports</strong> list, click the ⋮ action on a report and select <strong>Export ESIA PDF</strong>. The PDF includes all data from Steps 1–12 for the linked ESIA project.</p>
</div>

<!-- ═══════════════════ MODULE 10: ENV AUDIT ═══════════════════ -->
<div class="section" id="m10">
  <div class="section-header">
    <div class="section-num">13</div>
    <div><div class="section-title">Module 10 — Environmental Audit Management</div>
    <div class="section-desc">ISO 14001 compliance audits with 42-item checklist</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Lead Auditor</span>
  </div>

  <div class="workflow">
    <div class="wf-box gray">Planned</div><div class="wf-arrow">→</div>
    <div class="wf-box">In Progress</div><div class="wf-arrow">→</div>
    <div class="wf-box orange">Under Review</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Completed</div><div class="wf-arrow">→</div>
    <div class="wf-box gray">Closed</div>
  </div>

  <h3>Creating an Environmental Audit</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Environmental Audit → Environmental Audits</strong> and click <strong>New Environmental Audit</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">The system auto-generates an <strong>Audit Number</strong>. Select <strong>Audit Type</strong> (Internal / External / Compliance / Regulatory / Supplier) and <strong>Audit Method</strong> (On-site / Remote / Hybrid).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter <strong>Audit Title</strong>, <strong>Scope</strong>, <strong>Objectives</strong>, and <strong>Criteria</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Set <strong>Site Location</strong>, link to <strong>Project</strong> and <strong>Department</strong>, and schedule dates.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Assign the <strong>Audit Team</strong>: Team Leader, Lead Auditor, Co-Auditors, Technical Experts, and Auditee Representatives.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">Save with status <em>Planned</em>.</div></div>
  </div>

  <h3>Using the Audit Checklist (Checklist Items Tab)</h3>
  <p>The checklist contains up to 42 items across ISO 14001 clauses (categories A through I).</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Open the audit and click the <strong>Checklist Items</strong> tab.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">For each checklist item, set <strong>Response</strong>: Compliant / Non-Compliant / Partial / Not Applicable / Not Assessed.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter <strong>Evidence Notes</strong> (what you observed) and <strong>Auditor Notes</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">The <strong>Compliance Score</strong> updates automatically as you complete checklist items.</div></div>
  </div>

  <h3>Recording Findings (Findings Tab)</h3>
  <p>For each non-conformity or observation found during the audit, add a finding:</p>
  <ul>
    <li>Select <strong>Finding Type</strong> (Major NC, Minor NC, Observation, Opportunity for Improvement)</li>
    <li>Describe the <strong>Finding</strong>, reference the <strong>ISO 14001 Clause</strong>, and enter evidence</li>
    <li>Assign corrective actions and a <strong>Due Date</strong></li>
  </ul>

  <h3>Compliance Rating Scale</h3>
  <table>
    <tr><th>Compliance Score</th><th>Rating</th></tr>
    <tr><td>90–100%</td><td>Excellent</td></tr>
    <tr><td>75–89%</td><td>Good</td></tr>
    <tr><td>50–74%</td><td>Fair</td></tr>
    <tr><td>&lt; 50%</td><td>Poor — Immediate management review required</td></tr>
  </table>

  <h3>Completing and Closing</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Enter <strong>Management Summary</strong> and <strong>Closing Notes</strong> in the Summary section.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Set <strong>Approved By</strong> and <strong>Approval Date</strong>. Status moves to <em>Completed</em>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Download the full audit report via the <strong>Environmental Audit PDF</strong> row action.</div></div>
  </div>
</div>

<!-- ═══════════════════ MODULE 11: AMS ═══════════════════ -->
<div class="section" id="m11">
  <div class="section-header">
    <div class="section-num">14</div>
    <div><div class="section-title">Module 11 — Audit Management System (AMS)</div>
    <div class="section-desc">ISO 9001 / 14001 / 45001 / 50001 internal audit management</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-all">Lead Auditor</span>
  </div>

  <p>The AMS manages the full lifecycle of internal management system audits across four ISO standards. It includes ISO-specific checklists, non-conformity (NC) tracking with root cause analysis (RCA), and CAPA management.</p>

  <div class="workflow">
    <div class="wf-box gray">Planned</div><div class="wf-arrow">→</div>
    <div class="wf-box">In Progress</div><div class="wf-arrow">→</div>
    <div class="wf-box green">Completed</div><div class="wf-arrow">→</div>
    <div class="wf-box gray">Closed</div>
  </div>

  <h3>Creating an Internal Audit</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Audit Management System → Internal Audits</strong> and click <strong>New Internal Audit</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">The system auto-generates an <strong>Audit Reference</strong> number.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Select <strong>Audit Type</strong> (Internal / External / Certification / Surveillance / Supplier) and <strong>Standard</strong> (ISO 9001 / ISO 14001 / ISO 45001 / ISO 50001 / Client-Specific / Other).</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Enter <strong>Scope</strong>, <strong>Objectives</strong>, <strong>Criteria</strong>, <strong>Audit Location</strong>, and <strong>Auditee Representative</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Link to <strong>Project</strong> and <strong>Department</strong>. Schedule <strong>Audit Date</strong>, <strong>Planned Start</strong>, and <strong>End Date</strong>.</div></div>
    <div class="step"><div class="step-num">6</div><div class="step-body">Assign the <strong>Lead Auditor</strong>. Save with status <em>Planned</em>.</div></div>
  </div>

  <h3>Loading ISO Checklists (Checklist Items Tab)</h3>
  <p>Each standard has a pre-built 16-item ISO clause checklist that can be loaded automatically.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Open the audit and click the <strong>Checklist Items</strong> tab.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Click the <strong>Seed ISO 9001 Template</strong> (or 14001 / 45001 / 50001) button — this instantly loads the 16 standard checklist items for that ISO standard.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">For each item, set <strong>Response</strong>: Compliant / Non-Compliant / Partial / Not Applicable / Not Assessed.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Add <strong>Score</strong> (0–5), <strong>Evidence Notes</strong>, and <strong>Auditor Notes</strong>. The <strong>Compliance Score</strong> updates automatically.</div></div>
  </div>

  <h3>Recording Non-Conformities (Non-Conformities Tab)</h3>
  <p>A Non-Conformity (NC) is a failure to meet a requirement of the management system standard.</p>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">In the <strong>Non-Conformities</strong> tab, click <strong>New Non-Conformity</strong>. The system auto-assigns an NC number.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select <strong>NC Type</strong> (Major / Minor / Observation / OFI), enter <strong>Description</strong>, <strong>ISO Clause Reference</strong>, and <strong>Evidence</strong>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Set <strong>Likelihood</strong> and <strong>Severity</strong> (1–5 each). The system calculates <strong>Risk Score</strong> (L × S) and assigns a <strong>Risk Level</strong> automatically.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Root Cause Analysis (RCA)</strong> section: choose <strong>RCA Method</strong> (5 Whys or Fishbone). For 5 Whys, answer Why 1 through Why 5 and enter the <strong>Root Cause Summary</strong>. For Fishbone, fill in all 6 cause categories.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Assign to a <strong>Responsible Person</strong>, set <strong>Due Date</strong>. Save.</div></div>
  </div>

  <h3>CAPA Actions (CAPA Tab)</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">In the <strong>CAPA Actions</strong> tab, click <strong>New CAPA Action</strong>. The system auto-assigns a CAPA number.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Link to the relevant <strong>Non-Conformity</strong>, select <strong>Action Type</strong> (Corrective / Preventive / Improvement).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Describe the <strong>Action</strong>, what <strong>Root Cause it Addresses</strong>, and assign a <strong>Responsible Person</strong>, <strong>Department</strong>, and <strong>Target Date</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Track implementation: update <strong>Status</strong> (Open → In Progress → Completed → Verified) and add <strong>Evidence Notes</strong> when done.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">When complete, the verifier sets <strong>Verification Status</strong> (Passed / Failed / Not Due).</div></div>
  </div>

  <h3>Lead Auditor Approval</h3>
  <p>In the <strong>Lead Auditor Approval</strong> section on the main audit form, the Lead Auditor sets <strong>Approved By</strong>, <strong>Approval Date</strong>, and the audit moves to status <em>Completed</em>.</p>

  <h3>AMS Audit Report PDF</h3>
  <p>Click the <strong>AMS Report PDF</strong> row action to download a comprehensive audit report including: Audit Scorecard, Full Checklist, NC Register with RCA, CAPA Register, and Signatures page.</p>
</div>

<!-- ═══════════════════ MODULE 12: TRAINING ═══════════════════ -->
<div class="section" id="m12">
  <div class="section-header">
    <div class="section-num">15</div>
    <div><div class="section-title">Module 12 — Training &amp; Competency</div>
    <div class="section-desc">Training records, certifications, and competency assessments</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-hr">HR Director</span>
  </div>

  <h3>12.1 Training Records</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Training &amp; Competency → Training Records</strong> and click <strong>New Training Record</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select the <strong>Staff Member</strong> and <strong>Training Type</strong>: Induction, Refresher, Toolbox Talk, External, Certification, E-Learning, or Drill/Exercise.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter <strong>Training Title</strong>, <strong>Topic</strong>, <strong>Provider</strong>, <strong>Conducted By</strong>, <strong>Date Attended</strong>, and <strong>Duration (hours)</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Record <strong>Result</strong> (Passed / Failed / Not Assessed), <strong>Certificate Number</strong>, and <strong>Expiry Date</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Set <strong>Verified By</strong> and add any notes. Save.</div></div>
  </div>

  <h3>12.2 Certifications</h3>
  <p>Tracks professional qualifications and statutory certifications (e.g., NEBOSH, First Aid, Forklift Licence).</p>
  <ul>
    <li>Enter <strong>Certification Name</strong>, <strong>Issuing Body</strong>, <strong>Issue Date</strong>, <strong>Expiry Date</strong>, and <strong>Certificate Number</strong></li>
    <li>Status (Active / Expired / Suspended) updates automatically based on expiry date</li>
    <li>The Dashboard <strong>Expiring Documents</strong> widget highlights certifications due for renewal</li>
    <li>Attach the certificate file for digital record-keeping</li>
  </ul>

  <h3>12.3 Competency Assessments</h3>
  <p>Formal assessment of whether staff have demonstrated the competency required for their role.</p>
  <ul>
    <li>Link to a <strong>Staff Member</strong> and specify the <strong>Competency Area</strong> being assessed</li>
    <li>Record <strong>Assessment Method</strong> (observation, written test, practical), <strong>Assessor</strong>, <strong>Date</strong>, and <strong>Outcome</strong></li>
    <li>Set a <strong>Reassessment Date</strong> for periodic revalidation</li>
  </ul>
</div>

<!-- ═══════════════════ MODULE 13: HR ═══════════════════ -->
<div class="section" id="m13">
  <div class="section-header">
    <div class="section-num">16</div>
    <div><div class="section-title">Module 13 — HR &amp; Payroll</div>
    <div class="section-desc">Staff records, leave management, and payroll processing</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hr">HR Director</span>
    <span class="role role-acc">Accountant</span>
    <span class="role role-all">Employee (Leave / Expenses only)</span>
  </div>

  <h3>13.1 Staff Management</h3>
  <ul>
    <li>Go to <strong>HR &amp; Payroll → Staff</strong> to view and manage all staff records</li>
    <li>Each staff profile includes: personal details, employment type, department, role, and emergency contacts</li>
    <li>Tabs on the staff edit page give access to: <strong>Attendance</strong>, <strong>Leave Requests</strong>, and <strong>Payroll</strong> history</li>
  </ul>

  <h3>13.2 Leave Requests</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Employee:</strong> Go to <strong>HR &amp; Payroll → Leave Requests → New Leave Request</strong>. Select <strong>Leave Type</strong>, enter <strong>From</strong> and <strong>To</strong> dates and a <strong>Reason</strong>. Submit.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Supervisor / HR Director:</strong> Open the request, review details, and set status to <em>Approved</em> or <em>Rejected</em>.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Approved leave is reflected in payroll calculations for the relevant period.</div></div>
  </div>

  <h3>13.3 Payroll Processing</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>HR &amp; Payroll → Payroll</strong> and click <strong>New Payroll</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Select the <strong>Staff Member</strong>, <strong>Payroll Period</strong> (month), and <strong>Employment Type</strong> (permanent/casual/contractor).</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Enter <strong>Basic Salary</strong>, <strong>Allowances</strong>, <strong>Overtime Pay</strong>, and <strong>Bonus</strong>. The system calculates <strong>Gross Salary</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">Statutory deductions are auto-calculated: <strong>PAYE</strong>, <strong>NSSF (Employee + Employer)</strong>, <strong>WCF</strong>, <strong>NHIF</strong>. Enter any <strong>Loan Deductions</strong> or <strong>Other Deductions</strong>.</div></div>
    <div class="step"><div class="step-num">5</div><div class="step-body">Review the calculated <strong>Net Salary</strong>. Set status to <em>Pending</em> then <em>Paid</em> once payment is made. Enter <strong>Payment Reference</strong> and <strong>Payment Date</strong>.</div></div>
  </div>

  <div class="info">The <strong>Monthly Financial Summary</strong> (Finance menu) aggregates all payroll data for the selected period alongside revenues and expenses.</div>
</div>

<!-- ═══════════════════ MODULE 14: FINANCE ═══════════════════ -->
<div class="section" id="m14">
  <div class="section-header">
    <div class="section-num">17</div>
    <div><div class="section-title">Module 14 — Finance &amp; Expenses</div>
    <div class="section-desc">Invoices, field expenses, petty cash, and management reports</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-acc">Accountant</span>
    <span class="role role-hr">HR Director (Reports)</span>
    <span class="role role-all">Field Staff (Expenses)</span>
  </div>

  <h3>14.1 Invoices</h3>
  <ul>
    <li>Go to <strong>Finance &amp; Expenses → Invoices</strong> to manage client invoices</li>
    <li>Link invoices to a <strong>Project</strong> and <strong>Client</strong>. Enter line items, tax, and payment terms</li>
    <li>Track invoice status: Draft → Sent → Paid → Overdue</li>
  </ul>

  <h3>14.2 Field Expenses</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Field Staff:</strong> Go to <strong>Finance &amp; Expenses → Field Expenses → New Field Expense</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Link to the <strong>Project</strong>, select <strong>Category</strong>, enter <strong>Amount</strong>, <strong>Date</strong>, and <strong>Description</strong>. Attach receipts.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Accountant:</strong> Reviews and sets status to <em>Approved</em> or <em>Rejected</em>. Approved expenses are included in management reports.</div></div>
  </div>

  <h3>14.3 Petty Cash</h3>
  <ul>
    <li>Record day-to-day small cash payments under <strong>Petty Cash Transactions</strong></li>
    <li>Each transaction captures: date, payee, purpose, amount, and receipt</li>
    <li>Reconcile monthly — totals appear in the Monthly Financial Summary</li>
  </ul>

  <h3>14.4 Management Reports</h3>
  <p>Go to <strong>Finance &amp; Expenses → Management Reports</strong>. Available report downloads (CSV or PDF):</p>
  <table>
    <tr><th>Report</th><th>Content</th></tr>
    <tr><td>Monthly Payroll Summary</td><td>All staff gross, net, deductions for a month</td></tr>
    <tr><td>Payroll by Employment Type</td><td>Cost split between permanent, casual, contractor</td></tr>
    <tr><td>Staff Cost by Department</td><td>Total cost breakdown per department</td></tr>
    <tr><td>Staff Cost by Project</td><td>Total cost allocation per project</td></tr>
    <tr><td>Employee Earnings History</td><td>Full year earnings for one staff member</td></tr>
    <tr><td>Field Expenses by Project</td><td>Approved field costs per project</td></tr>
    <tr><td>Field Expenses by Category</td><td>Spend breakdown by expense category</td></tr>
    <tr><td>Trial Balance</td><td>Chart of accounts ledger balances</td></tr>
  </table>

  <h3>14.5 Monthly Financial Summary</h3>
  <p>Go to <strong>Finance &amp; Expenses → Monthly Financial Summary</strong>. Select a month and click <strong>Export PDF</strong> to download a consolidated one-page financial report showing Revenue, Total Outflows, Net Position, Payroll, Field Expenses, Petty Cash, and a reconciliation table.</p>
</div>

<!-- ═══════════════════ MODULE 15: DOCUMENTS ═══════════════════ -->
<div class="section" id="m15">
  <div class="section-header">
    <div class="section-num">18</div>
    <div><div class="section-title">Module 15 — Document Control</div>
    <div class="section-desc">Corporate documents with version control</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
    <span class="role role-esg">ESG Officer</span>
    <span class="role role-hr">HR Director</span>
  </div>

  <ul>
    <li>Go to <strong>Document Control → Documents</strong> to manage all controlled documents</li>
    <li>Supported document types: Policies, Procedures, Work Instructions, HSE Plans, Checklists, Forms, Registers</li>
    <li>Each document captures: <strong>Title</strong>, <strong>Document Number</strong>, <strong>Category</strong>, <strong>Version</strong>, <strong>Issue Date</strong>, <strong>Expiry/Review Date</strong>, and the <strong>File</strong></li>
    <li>The <strong>Revisions tab</strong> on each document tracks the history of all previous versions with change notes</li>
    <li>The Dashboard <strong>Expiring Documents</strong> widget lists all documents whose review date falls within the next 30 days</li>
    <li>Documents can be linked to a specific project for project-level document management</li>
  </ul>

  <div class="tip"><strong>Best practice:</strong> Set <strong>Review Date</strong> to 1 year from issue. Assign a <strong>Document Owner</strong> who is responsible for initiating the review.</div>
</div>

<!-- ═══════════════════ MODULE 16: ENERGY ═══════════════════ -->
<div class="section" id="m16">
  <div class="section-header">
    <div class="section-num">19</div>
    <div><div class="section-title">Module 16 — Energy Management (EnMS)</div>
    <div class="section-desc">ISO 50001-aligned energy consumption and performance tracking</div></div>
  </div>
  <div class="roles">
    <span class="role role-md">MD</span>
    <span class="role role-hse">HSE Staff</span>
  </div>

  <p>The Energy Management module supports ISO 50001 implementation across four components:</p>

  <h3>16.1 Consumption Records</h3>
  <ul>
    <li>Log monthly energy consumption by energy source (electricity, diesel, petrol, LPG, solar, grid)</li>
    <li>Enter <strong>Site/Equipment</strong>, <strong>Period</strong>, <strong>Quantity</strong>, <strong>Unit</strong>, and <strong>Cost</strong></li>
    <li>Automatic conversion to <strong>kWh equivalent</strong> for comparison</li>
  </ul>

  <h3>16.2 Energy Performance Indicators (EnPI)</h3>
  <ul>
    <li>Define key performance indicators (e.g., kWh per tonne produced, fuel per vehicle-km)</li>
    <li>Track actual vs. target performance over time</li>
  </ul>

  <h3>16.3 Energy Baselines</h3>
  <ul>
    <li>Establish the reference period against which energy performance improvements are measured</li>
    <li>Enter baseline year, total energy consumption, and associated production/output data</li>
  </ul>

  <h3>16.4 Energy Action Plans</h3>
  <ul>
    <li>Create action plans to achieve energy savings targets</li>
    <li>Each action specifies: <strong>Description</strong>, <strong>Expected Saving</strong>, <strong>Responsible Person</strong>, <strong>Target Date</strong>, and <strong>Status</strong></li>
  </ul>
</div>

<!-- ═══════════════════ ADMIN ═══════════════════ -->
<div class="section" id="admin">
  <div class="section-header">
    <div class="section-num">20</div>
    <div><div class="section-title">System Administration</div>
    <div class="section-desc">User management, departments, and company settings</div></div>
  </div>
  <div class="roles">
    <span class="role role-admin">System Admin</span>
    <span class="role role-md">MD</span>
  </div>

  <h3>Managing Users</h3>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-body">Go to <strong>Dashboard &amp; Core Admin → Users</strong> and click <strong>New User</strong>.</div></div>
    <div class="step"><div class="step-num">2</div><div class="step-body">Enter <strong>Name</strong>, <strong>Email</strong>, and assign an initial <strong>Password</strong>. The user will be prompted to change it on first login.</div></div>
    <div class="step"><div class="step-num">3</div><div class="step-body">Assign one or more <strong>Roles</strong> from the roles list. The user's sidebar will immediately reflect the permissions of their assigned roles.</div></div>
    <div class="step"><div class="step-num">4</div><div class="step-body">To deactivate a user, edit their account and unassign all roles, or disable the account.</div></div>
  </div>

  <h3>Managing Departments</h3>
  <ul>
    <li>Go to <strong>Dashboard &amp; Core Admin → Departments</strong> to add or edit department names</li>
    <li>Departments are used across Staff records, HAZOP Studies, Permits, Audits, and HIRA records</li>
  </ul>

  <h3>Company Settings</h3>
  <ul>
    <li>Go to <strong>Settings → Company Settings</strong></li>
    <li>Upload your <strong>Company Logo</strong> — this logo appears on all PDF exports (HIRA reports, audit reports, PTW permits, ESIA reports, etc.)</li>
    <li>Set <strong>Company Name</strong> and <strong>Company Tagline</strong> shown on PDF headers and footers</li>
  </ul>

  <div class="warn"><strong>Important:</strong> After uploading a new logo, all subsequent PDF exports will use the new logo immediately. Old PDFs that have already been downloaded are not affected.</div>
</div>

<!-- ═══════════════════ FOOTER ═══════════════════ -->
<div style="background:#0f172a;color:#94a3b8;padding:24px 60px;font-size:8.5pt;display:flex;justify-content:space-between;align-items:center">
  <span>PortalHSE Training Manual · Version 1.0 · June 2026</span>
  <span>Confidential — Internal Use Only · © {{ date('Y') }} PortalHSE</span>
</div>

</div><!-- /wrapper -->
</body>
</html>

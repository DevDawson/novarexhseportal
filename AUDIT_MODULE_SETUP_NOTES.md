# Internal / Lead Auditor Module — Setup Notes
## Module 2 — ISO 9001 / 14001 / 45001 Internal Audits

---

### What Was Built

| File | Purpose |
|------|---------|
| `database/migrations/2024_01_13_000002_create_internal_audits_table.php` | Creates `internal_audits` table |
| `database/migrations/2024_01_13_000003_create_audit_team_members_table.php` | Creates `audit_team_members` pivot table |
| `database/migrations/2024_01_13_000004_create_audit_findings_table.php` | Creates `audit_findings` table |
| `app/Models/InternalAudit.php` | Model with auto-reference generation and relations |
| `app/Models/AuditFinding.php` | Model with relations and constants |
| `app/Filament/Resources/InternalAuditResource.php` | Full Filament resource |
| `app/Filament/Resources/InternalAuditResource/Pages/ListInternalAudits.php` | List page |
| `app/Filament/Resources/InternalAuditResource/Pages/CreateInternalAudit.php` | Create page |
| `app/Filament/Resources/InternalAuditResource/Pages/EditInternalAudit.php` | Edit page |
| `app/Filament/Resources/InternalAuditResource/RelationManagers/FindingsRelationManager.php` | Findings sub-table on audit edit page |
| `app/Filament/Widgets/OpenAuditFindingsWidget.php` | Dashboard widget — open NCs |
| `database/seeders/RoleSeeder.php` | *(modified)* Added `manage audits` permission |
| `app/Providers/Filament/AdminPanelProvider.php` | *(modified)* Registered `OpenAuditFindingsWidget` |

---

### Install Steps

```bash
# 1. Run the new migrations (3 new tables)
php artisan migrate

# 2. Re-seed roles & permissions
php artisan db:seed --class=RoleSeeder

# 3. Clear all caches
php artisan optimize:clear
```

> **Prerequisite:** The `departments` table must exist (it is referenced as a nullable FK in `internal_audits`). This table is created by the HR module which was already built. If running migrations on a fresh DB, ensure all prior migrations run first.

---

### How It Works

#### Distinction from `esia_audits`
The existing `esia_audits` table covers **ESIA/environmental/social field assessments** (project-specific regulatory compliance work). This new module covers **management-system audits** — ISO 9001/14001/45001 internal audits, certification audits, surveillance visits, and supplier audits. These are distinct record types with different workflows.

---

#### Internal Audit Record (`internal_audits`)

**Form sections:**

1. **Audit Details** — Audit reference (auto-generated, read-only in form), audit type (Internal/External/Certification/Surveillance/Supplier), standard (ISO 9001/14001/45001/Client-specific/Other — "Other" reveals a free-text field), scope, project (optional), department (optional), audit date, lead auditor (required), status.

2. **Audit Team** — Multi-select of `users` via the `teamMembers` BelongsToMany relationship. The Lead Auditor is captured on the main form and is NOT duplicated here.

3. **Summary & Report** — Free-text summary/conclusion, and a FileUpload for the PDF/Word audit report stored in `storage/app/public/audits/reports/`.

**Auto-reference generation:** `InternalAudit::booted()` fires a `creating` listener that calls `InternalAudit::nextReference(now())` if `audit_reference` is blank. Format: `AUD-{YYYY}-{MM}-{NNNN}` (e.g. `AUD-2026-06-0001`). Mirrors `JournalEntry::nextReference()` exactly.

**Table columns:** Reference (bold), Audit Type badge (color-coded), Standard (shows `standard_other` text for "Other"), Audit Date, Lead Auditor, Findings summary (e.g. "5 findings (2 NC)" — red if NCs exist), Status badge.

---

#### Findings (`audit_findings`) — RelationManager

Accessed from the **Edit** page of an audit as a sub-table below the main form. Supports full CRUD.

**Finding types and colors:**
| Type | Badge Color |
|------|------------|
| Major Nonconformity | Red (danger) |
| Minor Nonconformity | Amber (warning) |
| Conformity | Green (success) |
| Opportunity for Improvement | Blue (info) |
| Observation | Gray |

**Verification section:** Shown conditionally when the finding status is `closed` or `verified`. Captures verification notes and date.

**Table highlights:** Target Date turns red if past and finding is not yet closed/verified.

---

#### OpenAuditFindingsWidget (Dashboard)
Visible to `md`, `hse_staff`, `business_director`. Shows all findings where:
- `finding_type IN ('minor_nonconformity', 'major_nonconformity')`
- `status NOT IN ('closed', 'verified')`

Ordered by finding type then target date. Each row has an "Open Audit" action that links directly to the parent audit's Edit page (where the findings relation manager is visible).

---

### Permissions

| Permission | Roles Granted |
|-----------|--------------|
| `manage audits` | `md`, `hse_staff`, `business_director` |

---

### Navigation

Appears under **HSE & Technical Operations** navigation group, labelled "Internal Audits", with sort order 5.

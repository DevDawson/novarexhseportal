# NovarexHSE Portal

An integrated **HSE + EMS + ESG** management system built on **Laravel 11 + Filament v3** for Novarex Ltd.

---

## Modules

| Module | Description |
|--------|-------------|
| **HSE** | Incidents, Corrective Actions, HIRA, Permits, ESIA Audits |
| **Internal Audit** | ISO 9001/14001/45001 audit planning, findings & NCRs |
| **EMS** | Environmental Aspects, Legal Register, Monitoring Records |
| **ESG** | Stakeholders, Engagement Log, Grievances, Social Indicators, Policy Register, Ethics Incidents, Targets |
| **Finance** | Invoices, Field Expenses, Petty Cash, Payroll |
| **HR** | Staff, Leave Requests, Departments |
| **Projects** | Projects, Deliverables, Permit to Work |

---

## Requirements

- PHP 8.2+
- MariaDB 10.4+ / MySQL 8+
- Composer 2
- Node.js 20+ (for Vite/assets)

---

## Installation

```bash
git clone https://github.com/YOUR_ORG/portalhse.git
cd portalhse

composer install
cp .env.example .env
php artisan key:generate

# Configure DB + mail in .env, then:
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan storage:link
php artisan optimize:clear

npm install && npm run build
```

---

## Key Technologies

- **Laravel 11** — framework
- **Filament v3** — admin panel
- **spatie/laravel-permission** — role-based access control
- **barryvdh/laravel-dompdf** — PDF export
- **Laravel Notifications** — in-app (database) + email alerts

---

## Roles

| Role | Access |
|------|--------|
| `md` | Full system access |
| `hse_staff` | HSE, Incidents, HIRA, Audits, EMS |
| `esg_officer` | ESG module (Stakeholders, Grievances, Targets, Policies) |
| `hr_director` | HR, Staff, Leave, Payroll |
| `business_director` | BD, Tenders, Projects, ESG Targets |
| `accountant` | Finance, Invoices, Payroll |
| `field_staff` | Leave requests (own only), Field Expenses |
| `secretary` | Documents, Leave, Expenses |
| `it_technician` | Users, Settings |

---

## PDF Exports

Each assessment type has a row-level **PDF** button:

| Report | URL Pattern |
|--------|-------------|
| HIRA Risk Assessment | `GET /pdf/hira/{hazard}` |
| Internal Audit Report | `GET /pdf/audit/{audit}` |
| Incident Report | `GET /pdf/incident/{incident}` |
| EMS Environmental Aspect | `GET /pdf/ems/aspect/{aspect}` |
| ESG Summary | `GET /pdf/esg/summary` |

All routes require authentication + the relevant permission.

---

## Notifications

The following events trigger **in-app bell + email** notifications:

| Event | Recipients |
|-------|-----------|
| New incident reported | MD, HSE Staff |
| Leave request submitted | MD, HR Director |
| Leave request approved/rejected | Requesting staff member |
| New grievance submitted | MD, ESG Officers |
| Audit finding overdue | (triggered manually or via scheduled command) |

Mail is sent from `support@novarex.co.tz`. Configure SMTP credentials in `.env`.

---

## Post-Deployment Checklist

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan storage:link
php artisan optimize:clear
php artisan queue:work   # for async email delivery
```

---

## Contact

**Novarex Ltd** — support@novarex.co.tz

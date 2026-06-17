<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\EmsKpiOverview;
use App\Filament\Widgets\AuditManagementKpiWidget;
use App\Filament\Widgets\EnvironmentalAuditKpiWidget;
use App\Filament\Widgets\EsiaOverviewWidget;
use App\Filament\Widgets\EnvironmentalMetricsTrendChart;
use App\Filament\Widgets\EsgKpiOverview;
use App\Filament\Widgets\EsgTargetsProgressWidget;
use App\Filament\Widgets\ExpiringDocumentsWidget;
use App\Filament\Widgets\ExpiringLicensesWidget;
use App\Filament\Widgets\ExpiringPermitsWidget;
use App\Filament\Widgets\HazidKpiWidget;
use App\Filament\Widgets\HazopKpiWidget;
use App\Filament\Widgets\PtwKpiWidget;
use App\Filament\Widgets\HighRiskHazardsWidget;
use App\Filament\Widgets\HseKpiOverview;
use App\Filament\Widgets\IncidentSeverityChart;
use App\Filament\Widgets\IncidentTrendChart;
use App\Filament\Widgets\OpenAuditFindingsWidget;
use App\Filament\Widgets\OpenCorrectiveActionsWidget;
use App\Filament\Widgets\OpenGrievancesWidget;
use App\Filament\Widgets\ProjectSafetyPerformanceWidget;
use App\Filament\Widgets\RevenueVsExpensesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
                'success' => Color::Green,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

            ->navigationGroups([
                NavigationGroup::make('HSE System'),
                NavigationGroup::make('Incident Management'),
                NavigationGroup::make('Risk Assessment (HAZID)'),
                NavigationGroup::make('Risk Assessment (HAZOP)'),
                NavigationGroup::make('HIRA'),
                NavigationGroup::make('Permit to Work (PTW)'),
                NavigationGroup::make('Environmental Management (EMS)'),
                NavigationGroup::make('ESG Management'),
                NavigationGroup::make('EIA / ESIA'),
                NavigationGroup::make('Environmental Audit'),
                NavigationGroup::make('Audit Management System'),
            ])

            // Auto-discover Resources and Pages (fine).
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

            ->pages([
                Pages\Dashboard::class,
            ])

            ->navigationItems([
                NavigationItem::make('Training Manual')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn () => route('training.manual'))
                    ->openUrlInNewTab()
                    ->group('Settings')
                    ->sort(99),
            ])

            // DO NOT use discoverWidgets() together with ->widgets([]).
            // discoverWidgets() would register every widget in the folder,
            // then ->widgets([]) would register them AGAIN = duplicates, and
            // Filament's own AccountWidget / FilamentInfoWidget would also
            // appear for every role.
            //
            // Instead, we list widgets explicitly here so we control:
            //   (a) which widgets appear
            //   (b) the order they appear on the Dashboard
            //
            // Each widget class has its own canView() method that gates
            // visibility by role.

            ->widgets([
                // 1. General Ops summary (visible to most operational roles)
                StatsOverview::class,

                // 2. HSE-specific KPIs (MD, HSE Staff, HR Director)
                HseKpiOverview::class,

                // 3. Financial trend (MD, Accountant, Business Director)
                RevenueVsExpensesChart::class,

                // 4. Incident breakdown charts (MD, HSE Staff, HR Director)
                IncidentSeverityChart::class,
                IncidentTrendChart::class,

                // 5. Corrective actions & safety performance (MD, HSE Staff)
                OpenCorrectiveActionsWidget::class,
                ProjectSafetyPerformanceWidget::class,

                // 6. Corporate document expiry alerts (anyone who can manage docs)
                ExpiringDocumentsWidget::class,

                // 7. Permit to Work - expiring/overdue permits (MD, HSE Staff)
                ExpiringPermitsWidget::class,

                // 8. HIRA - high/critical residual hazards (MD, HSE Staff)
                HighRiskHazardsWidget::class,

                // 8b. HAZID KPI overview — 10 headline metrics (MD, HSE Staff)
                HazidKpiWidget::class,

                // 8c. HAZOP KPI overview — quantitative risk metrics (MD, HSE Staff)
                HazopKpiWidget::class,

                // 8d. PTW KPI overview — active permits, risk, compliance (MD, HSE Staff)
                PtwKpiWidget::class,

                // 9. Audit Module - open nonconformities (MD, HSE Staff, Business Director)
                OpenAuditFindingsWidget::class,

                // 10. EMS KPI stats (MD, HSE Staff, Business Director)
                EmsKpiOverview::class,

                // 11. EMS environmental metrics trend chart (MD, HSE Staff, Business Director)
                EnvironmentalMetricsTrendChart::class,

                // 12. EMS expiring licences/permits (MD, HSE Staff)
                ExpiringLicensesWidget::class,

                // 13. ESG KPI stats (MD, ESG Officer, Business Director)
                EsgKpiOverview::class,

                // 14. ESG targets progress table (MD, ESG Officer, Business Director)
                EsgTargetsProgressWidget::class,

                // 15. Open grievances (MD, ESG Officer)
                OpenGrievancesWidget::class,

                // 16. EIA/ESIA overview KPIs (MD, HSE Staff, Business Director)
                EsiaOverviewWidget::class,

                // 17. AMS KPIs — ISO 9001/14001/45001/50001 (MD, HSE Staff, Business Director)
                AuditManagementKpiWidget::class,

                // 18. Environmental Audit KPIs — ISO 14001 (MD, HSE Staff, Business Director)
                EnvironmentalAuditKpiWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

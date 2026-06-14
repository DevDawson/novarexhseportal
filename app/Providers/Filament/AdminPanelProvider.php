<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ExpiringDocumentsWidget;
use App\Filament\Widgets\HseKpiOverview;
use App\Filament\Widgets\IncidentSeverityChart;
use App\Filament\Widgets\IncidentTrendChart;
use App\Filament\Widgets\OpenCorrectiveActionsWidget;
use App\Filament\Widgets\ProjectSafetyPerformanceWidget;
use App\Filament\Widgets\RevenueVsExpensesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

            // Auto-discover Resources and Pages (fine).
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

            ->pages([
                Pages\Dashboard::class,
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

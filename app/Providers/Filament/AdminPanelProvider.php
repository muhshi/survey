<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Survey BPS')
            ->login()
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->font('Outfit')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn () => new HtmlString('
                    <!-- SurveyJS Dependencies -->
                    <script src="https://unpkg.com/knockout/build/output/knockout-latest.js"></script>
                    
                    <!-- SurveyJS Core -->
                    <link href="https://unpkg.com/survey-core@1.12.61/defaultV2.min.css" type="text/css" rel="stylesheet">
                    <script src="https://unpkg.com/survey-core@1.12.61/survey.core.min.js"></script>
                    <script src="https://unpkg.com/survey-knockout-ui@1.12.61/survey-knockout-ui.min.js"></script>

                    <!-- SurveyJS Creator -->
                    <link href="https://unpkg.com/survey-creator-core@1.12.61/survey-creator-core.min.css" type="text/css" rel="stylesheet">
                    <script src="https://unpkg.com/survey-creator-core@1.12.61/survey-creator-core.min.js"></script>
                    <script src="https://unpkg.com/survey-creator-knockout@1.12.61/survey-creator-knockout.min.js"></script>

                    <!-- Translations -->
                    <script src="https://unpkg.com/survey-core@1.12.61/i18n/indonesian.js"></script>
                    <script src="https://unpkg.com/survey-core@1.12.61/survey.i18n.min.js"></script>
                    <script src="https://unpkg.com/survey-creator-core@1.12.61/i18n/indonesian.js"></script>
                    <script src="https://unpkg.com/survey-creator-core@1.12.61/survey-creator-core.i18n.min.js"></script>
                ')
            );
    }
}

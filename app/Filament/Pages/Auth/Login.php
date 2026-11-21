<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public bool $showFallback = false;

    public function mount(): void
    {
        parent::mount();

        if (request()->query('fallback') === '1' || request()->boolean('fallback')) {
            $this->showFallback = true;
        }
    }

    public function getHeading(): string|Htmlable
    {
        return 'Masuk ke '.config('app.name', 'Survey BPS');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Gunakan akun Single Sign-On (SSO) BPS Kabupaten Demak untuk melanjutkan.';
    }
}

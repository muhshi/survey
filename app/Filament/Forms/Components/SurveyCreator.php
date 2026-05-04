<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SurveyCreator extends Field
{
    protected string $view = 'filament.forms.components.survey-creator';

    public bool $isFullScreen = false;

    public function fullScreen(bool $condition = true): static
    {
        $this->isFullScreen = $condition;

        return $this;
    }

    public function isFullScreen(): bool
    {
        return $this->isFullScreen;
    }
}

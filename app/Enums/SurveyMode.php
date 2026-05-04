<?php

namespace App\Enums;

enum SurveyMode: string
{
    case Single = 'single';
    case Editable = 'editable';
    case Multi = 'multi';

    /**
     * Get human-readable label for the mode.
     */
    public function label(): string
    {
        return match ($this) {
            SurveyMode::Single => 'Sekali Isi (Tidak bisa diubah)',
            SurveyMode::Editable => 'Bisa Diedit',
            SurveyMode::Multi => 'Multi-Submit (Pendataan Lapangan)',
        };
    }
}

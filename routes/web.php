<?php

use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

// Landing Page
Route::get('/', [SurveyController::class, 'landing'])->name('home');

// SSO Routes
Route::get('/auth/sipetra/redirect', [SsoController::class, 'redirect'])->name('sipetra.login');
Route::get('/auth/sipetra/callback', [SsoController::class, 'callback']);

// Public Survey Routes
Route::get('/survei', [SurveyController::class, 'index'])->name('survey.index');
Route::get('/s/{survey:slug}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/s/{survey:slug}/submit', [SurveyController::class, 'submit'])->name('survey.submit');
Route::put('/s/{survey:slug}/edit', [SurveyController::class, 'updateSubmission'])->name('survey.update');

// Template Import User Route
Route::get('/survey-groups/template-import-user', function () {
    return Excel::download(
        new class implements FromArray
        {
            public function array(): array
            {
                return [
                    ['email', 'name'],
                    ['mitra1@example.com', 'Mitra Satu'],
                    ['mitra2@example.com', 'Mitra Dua'],
                ];
            }
        },
        'template_import_user.xlsx'
    );
})->middleware(['auth'])->name('survey.groups.template-import-user');

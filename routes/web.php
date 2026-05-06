<?php

use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

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

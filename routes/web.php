<?php

use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [SurveyController::class, 'landing'])->name('home');

// Public Survey Routes
Route::get('/survei', [SurveyController::class, 'index'])->name('survey.index');
Route::get('/s/{survey:slug}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/s/{survey:slug}/submit', [SurveyController::class, 'submit'])->name('survey.submit');
Route::put('/s/{survey:slug}/edit', [SurveyController::class, 'updateSubmission'])->name('survey.update');

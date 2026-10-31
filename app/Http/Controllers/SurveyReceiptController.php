<?php

namespace App\Http\Controllers;

use App\Models\JawabanResponden;
use App\Models\Survey;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurveyReceiptController extends Controller
{
    /**
     * Display the official submission receipt.
     */
    public function show(Request $request, Survey $survey, JawabanResponden $submission): View
    {
        // Ensure submission belongs to the survey
        if ($submission->survey_id !== $survey->id) {
            abort(404, 'Data pengisian tidak cocok dengan survei ini.');
        }

        // Authorization check
        if ($submission->user_id !== null) {
            $user = Auth::user();

            if (! $user) {
                // If not logged in, require token fallback if provided
                $expectedToken = hash_hmac('sha256', (string) $submission->id, config('app.key'));
                if ($request->query('token') !== $expectedToken) {
                    abort(403, 'Anda harus login untuk melihat tanda terima ini.');
                }
            } elseif ($user->id !== $submission->user_id && ! $user->hasAnyRole(['super_admin', 'admin'])) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat tanda terima peserta lain.');
            }
        }

        $payload = $submission->payload ?? [];

        // Determine smart respondent name
        $respondentName = $submission->user?->name
            ?? $payload['nama_lengkap']
            ?? $payload['nama']
            ?? $payload['name']
            ?? $payload['nama_peserta']
            ?? 'Anonim';

        // Additional info
        $opdOrInstansi = $payload['nama_dinas_opd']
            ?? $payload['instansi']
            ?? $payload['opd']
            ?? $submission->user?->unit_kerja
            ?? null;

        $emailOrContact = $submission->user?->email
            ?? $payload['email_peserta']
            ?? $payload['email']
            ?? $payload['nomor_hp']
            ?? $payload['no_hp']
            ?? null;

        // Registration number
        $regNumber = sprintf('BPS-%03d-%06d', $survey->id, $submission->id);

        // Verification URL
        $verifyUrl = route('survey.receipt', [$survey, $submission]);
        if ($submission->user_id === null) {
            // Keep public
        } else {
            $token = hash_hmac('sha256', (string) $submission->id, config('app.key'));
            $verifyUrl .= '?token='.$token;
        }

        // Generate QR code
        $qrCode = (new QRCode)->render($verifyUrl);

        // Quiz score details
        $isQuiz = (bool) $survey->is_quiz;
        $score = $submission->score;
        $passingScore = floatval($survey->settings['passing_score'] ?? 0);
        $passed = ($isQuiz && $score !== null && $passingScore > 0) ? ($score >= $passingScore) : null;

        return view('survey.receipt', compact(
            'survey',
            'submission',
            'respondentName',
            'opdOrInstansi',
            'emailOrContact',
            'regNumber',
            'verifyUrl',
            'qrCode',
            'isQuiz',
            'score',
            'passingScore',
            'passed'
        ));
    }
}

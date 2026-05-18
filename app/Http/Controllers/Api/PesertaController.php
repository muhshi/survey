<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JawabanResponden;
use App\Models\User;

class PesertaController extends Controller
{
    public function index()
    {
        // Ambil ID peserta yang sudah pernah di-submit di survei wawancara (ID 3)
        $submittedIds = JawabanResponden::where('survey_id', 3)
            ->get()
            ->pluck('payload.nama_peserta')
            ->filter()
            ->values()
            ->toArray();

        $data = User::role('calon_petugas')
            ->whereNotIn('id', $submittedIds)
            ->select('id', 'name', 'metadata')
            ->get()
            ->sortBy(fn ($item) => (int) $item->nomor_urut)
            ->values()
            ->map(fn ($item) => [
                'value' => $item->id,
                'text' => ($item->nomor_urut ? $item->nomor_urut.'. ' : '').$item->name,
            ]);

        return response()->json($data)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}

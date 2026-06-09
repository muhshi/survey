<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JawabanResponden;
use App\Models\MasterWilayah;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function kecamatan()
    {
        $data = MasterWilayah::select('nmkec', 'kdkec')
            ->distinct()
            ->orderByRaw('CAST(kdkec AS UNSIGNED) ASC')
            ->get()
            ->map(function ($item) {
                $kode = str_pad($item->kdkec, 3, '0', STR_PAD_LEFT);
                $nama = ucwords(strtolower($item->nmkec));

                return [
                    'value' => $item->nmkec, // Tetap menggunakan nmkec sebagai value agar filter desa tidak rusak
                    'text' => "$kode $nama",
                ];
            });

        return response()->json($data);
    }

    public function desa(Request $request)
    {
        $kecamatan = $request->query('kecamatan');

        $data = MasterWilayah::select('nmdesa', 'kddesa')
            ->where('nmkec', $kecamatan)
            ->distinct()
            ->orderByRaw('CAST(kddesa AS UNSIGNED) ASC')
            ->get()
            ->map(function ($item) {
                $kode = str_pad($item->kddesa, 3, '0', STR_PAD_LEFT);
                $nama = ucwords(strtolower($item->nmdesa));

                return [
                    'value' => $item->nmdesa,
                    'text' => "$kode $nama",
                ];
            });

        return response()->json($data);
    }

    public function sls(Request $request)
    {
        $desa = $request->query('desa');

        $data = MasterWilayah::select('nmsls', 'kdsls')
            ->where('nmdesa', $desa)
            ->distinct()
            ->orderByRaw('CAST(kdsls AS UNSIGNED) ASC')
            ->get()
            ->map(function ($item) {
                $kode = str_pad((string) $item->kdsls, 4, '0', STR_PAD_LEFT);
                $nama = $item->nmsls;

                return [
                    'value' => $item->nmsls,
                    'text' => "$kode $nama",
                ];
            });

        return response()->json($data);
    }

    public function subSls(Request $request)
    {
        $desa = $request->query('desa');
        $sls = $request->query('sls');

        $usedSubSls = [];
        $currentUserId = auth()->id();

        // Cari jawaban_responden yang berisi alokasi_wilayah
        $responses = JawabanResponden::where('payload', 'like', '%alokasi_wilayah%')->get();

        foreach ($responses as $resp) {
            // Jangan filter pilihan user yang sedang login agar tetap tampil saat mereka membuka ulang form
            if ($currentUserId && $resp->user_id === $currentUserId) {
                continue;
            }

            $payload = $resp->payload;
            if (isset($payload['alokasi_wilayah']) && is_array($payload['alokasi_wilayah'])) {
                foreach ($payload['alokasi_wilayah'] as $panel) {
                    if (isset($panel['sub_sls']) && is_array($panel['sub_sls'])) {
                        $usedSubSls = array_merge($usedSubSls, $panel['sub_sls']);
                    }
                }
            }
        }
        $usedSubSls = array_unique($usedSubSls);

        $data = MasterWilayah::select('idsubsls', 'kdsubsls', 'nama_ketua')
            ->where('nmdesa', $desa)
            ->where('nmsls', $sls)
            ->whereNotIn('idsubsls', $usedSubSls)
            ->orderByRaw('CAST(kdsubsls AS UNSIGNED) ASC')
            ->get()
            ->map(function ($item) {
                $kode = str_pad((string) $item->kdsubsls, 2, '0', STR_PAD_LEFT);
                $ketua = $item->nama_ketua ? ' - Ketua: '.ucwords(strtolower($item->nama_ketua)) : '';

                return [
                    'value' => $item->idsubsls,
                    'text' => "Sub SLS $kode$ketua",
                ];
            });

        return response()->json($data);
    }

    public function slsByKecamatan(Request $request)
    {
        $kecamatan = $request->query('kecamatan');

        $data = MasterWilayah::select('idsubsls', 'nmsls', 'nmdesa')
            ->where('nmkec', $kecamatan)
            ->orderBy('nmdesa')
            ->orderBy('nmsls')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->idsubsls,
                'text' => $item->nmdesa.' - '.$item->nmsls,
            ]);

        return response()->json($data);
    }
}

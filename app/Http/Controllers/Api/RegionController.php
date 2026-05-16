<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $data = MasterWilayah::select('idsubsls', 'nmsls')
            ->where('nmdesa', $desa)
            ->orderBy('nmsls')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->idsubsls,
                'text' => $item->nmsls,
            ]);

        return response()->json($data);
    }
}

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

        $data = MasterWilayah::select('idsubsls', 'kdsubsls', 'nama_ketua')
            ->where('nmdesa', $desa)
            ->where('nmsls', $sls)
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

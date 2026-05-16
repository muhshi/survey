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
            ->orderBy('nmkec')
            ->get()
            ->map(fn($item) => [
                'value' => $item->nmkec,
                'text' => $item->nmkec
            ]);

        return response()->json($data);
    }

    public function desa(Request $request)
    {
        $kecamatan = $request->query('kecamatan');

        $data = MasterWilayah::select('nmdesa', 'kddesa')
            ->where('nmkec', $kecamatan)
            ->distinct()
            ->orderBy('nmdesa')
            ->get()
            ->map(fn($item) => [
                'value' => $item->nmdesa,
                'text' => $item->nmdesa
            ]);

        return response()->json($data);
    }

    public function sls(Request $request)
    {
        $desa = $request->query('desa');

        $data = MasterWilayah::select('idsubsls', 'nmsls')
            ->where('nmdesa', $desa)
            ->orderBy('nmsls')
            ->get()
            ->map(fn($item) => [
                'value' => $item->idsubsls,
                'text' => $item->nmsls
            ]);

        return response()->json($data);
    }
}

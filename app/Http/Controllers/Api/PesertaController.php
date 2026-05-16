<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function index()
    {
        $data = User::role('calon_petugas')
            ->select('id', 'name', 'nomor_urut')
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'text' => $item->name,
                'nomor_urut' => $item->nomor_urut
            ]);

        return response()->json($data);
    }
}

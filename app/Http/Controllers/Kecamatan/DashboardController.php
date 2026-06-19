<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Pemilih;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $kecamatan = $user->kecamatan;

        // Hitung statistik tingkat kecamatan
        $totalPemilih = Pemilih::where('kecamatan_id', $kecamatan->id)->count();

        // Rekap per desa di kecamatan ini
        $perDesa = $kecamatan->desas()
            ->withCount('pemilihs')
            ->orderBy('nama')
            ->get()
            ->map(fn ($desa) => [
                'nama'  => $desa->nama,
                'total' => $desa->pemilihs_count,
            ]);

        return Inertia::render('kecamatan/Dashboard', [
            'kecamatan' => $kecamatan->nama,
            'stats' => [
                'total_pemilih' => $totalPemilih,
            ],
            'per_desa' => $perDesa,
        ]);
    }
}

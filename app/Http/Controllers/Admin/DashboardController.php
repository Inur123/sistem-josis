<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalPemilih    = Pemilih::count();
        $totalKecamatan  = Kecamatan::count();
        $totalDesa       = Desa::count();
        $totalAkun       = User::count();

        $perKecamatan = Kecamatan::withCount('pemilihs')
            ->orderBy('nama')
            ->get()
            ->map(fn ($kec) => [
                'nama'  => $kec->nama,
                'total' => $kec->pemilihs_count,
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_pemilih'   => $totalPemilih,
                'total_kecamatan' => $totalKecamatan,
                'total_desa'      => $totalDesa,
                'total_akun'      => $totalAkun,
            ],
            'per_kecamatan' => $perKecamatan,
        ]);
    }
}

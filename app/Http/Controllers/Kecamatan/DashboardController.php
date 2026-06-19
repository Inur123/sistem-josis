<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user        = $request->user();
        $kecamatanId = $user->kecamatan_id;

        $kecamatanNama = DB::table('kecamatans')
            ->where('id', $kecamatanId)
            ->value('nama');

        $totalPemilih = DB::table('pemilihs')
            ->where('kecamatan_id', $kecamatanId)
            ->count();

        $perDesa = DB::table('desas')
            ->leftJoin('pemilihs', 'pemilihs.desa_id', '=', 'desas.id')
            ->where('desas.kecamatan_id', $kecamatanId)
            ->select(
                'desas.nama',
                DB::raw('COUNT(pemilihs.id) as total'),
                DB::raw("SUM(CASE WHEN pemilihs.jenis_kelamin = 'L' THEN 1 ELSE 0 END) as l"),
                DB::raw("SUM(CASE WHEN pemilihs.jenis_kelamin = 'P' THEN 1 ELSE 0 END) as p")
            )
            ->groupBy('desas.id', 'desas.nama')
            ->orderBy('desas.nama', 'asc')
            ->get()
            ->map(fn ($desa) => [
                'nama'  => $desa->nama,
                'total' => (int) $desa->total,
                'l'     => (int) $desa->l,
                'p'     => (int) $desa->p,
            ]);

        $totalDesa = DB::table('desas')
            ->where('kecamatan_id', $kecamatanId)
            ->count();

        return Inertia::render('kecamatan/Dashboard', [
            'kecamatan' => $kecamatanNama,
            'stats'     => [
                'total_pemilih' => $totalPemilih,
                'total_desa'    => $totalDesa,
            ],
            'per_desa'  => $perDesa,
        ]);
    }
}

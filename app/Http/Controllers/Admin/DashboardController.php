<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $stats = [
            'total_pemilih' => DB::table('pemilihs')->count(),
            'total_kecamatan' => DB::table('kecamatans')->count(),
            'total_desa' => DB::table('desas')->count(),
            'total_akun' => DB::table('users')->count(),
        ];

        $perKecamatan = DB::table('kecamatans')
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($kec) {
                return [
                    'id' => $kec->id,
                    'nama' => $kec->nama,
                    'total' => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->count(),
                    'l' => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->where('jenis_kelamin', 'L')->count(),
                    'p' => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->where('jenis_kelamin', 'P')->count(),
                ];
            });

        $desaPerKecamatan = DB::table('desas')
            ->join('kecamatans', 'desas.kecamatan_id', '=', 'kecamatans.id')
            ->select('kecamatans.nama as kecamatan_nama', DB::raw('COUNT(desas.id) as total'))
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderBy('kecamatans.nama', 'asc')
            ->get()
            ->map(fn ($item) => [
                'kecamatan_nama' => $item->kecamatan_nama,
                'total' => (int) $item->total,
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'per_kecamatan' => $perKecamatan,
            'desa_per_kecamatan' => $desaPerKecamatan,
        ]);
    }
}

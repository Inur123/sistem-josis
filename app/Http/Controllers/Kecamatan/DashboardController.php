<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $kecamatanId = $user->kecamatan_id;
        $kecamatanNama = DB::table('kecamatans')->where('id', $kecamatanId)->value('nama');

        $totalPemilih = DB::table('pemilihs')->where('kecamatan_id', $kecamatanId)->where('status', 'terverifikasi')->count();
        $lakiLaki = DB::table('pemilihs')->where('kecamatan_id', $kecamatanId)->where('jenis_kelamin', 'L')->where('status', 'terverifikasi')->count();
        $perempuan = DB::table('pemilihs')->where('kecamatan_id', $kecamatanId)->where('jenis_kelamin', 'P')->where('status', 'terverifikasi')->count();
        $totalDesa = DB::table('desas')->where('kecamatan_id', $kecamatanId)->count();

        $perDesa = DB::table('desas')
            ->leftJoin('pemilihs', function ($join) {
                $join->on('pemilihs.desa_id', '=', 'desas.id')
                     ->where('pemilihs.status', '=', 'terverifikasi');
            })
            ->where('desas.kecamatan_id', $kecamatanId)
            ->select(
                'desas.id',
                'desas.nama',
                DB::raw('COUNT(pemilihs.id) as total'),
                DB::raw("SUM(CASE WHEN pemilihs.jenis_kelamin = 'L' THEN 1 ELSE 0 END) as l"),
                DB::raw("SUM(CASE WHEN pemilihs.jenis_kelamin = 'P' THEN 1 ELSE 0 END) as p")
            )
            ->groupBy('desas.id', 'desas.nama')
            ->orderBy('desas.nama', 'asc')
            ->get()
            ->map(fn ($desa) => [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'total' => (int) $desa->total,
                'l' => (int) $desa->l,
                'p' => (int) $desa->p,
            ]);

        $korcams = AnggotaTim::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('role', 'korcam')
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('nama')
            ->values();

        $kordes = AnggotaTim::query()
            ->with('desa')
            ->where('kecamatan_id', $kecamatanId)
            ->where('role', 'kordes')
            ->get()
            ->map(fn ($item) => [
                'nama' => $item->nama,
                'desa' => $item->desa?->nama ?? '-',
            ])
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return Inertia::render('kecamatan/Dashboard', [
            'kecamatan' => $kecamatanNama,
            'korcams' => $korcams,
            'kordes' => $kordes,
            'stats' => [
                'total_pemilih' => $totalPemilih,
                'laki_laki' => $lakiLaki,
                'perempuan' => $perempuan,
                'total_desa' => $totalDesa,
            ],
            'per_desa' => $perDesa,
        ]);
    }
}

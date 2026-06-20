<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get stats for Admin dashboard (global scope).
     *
     * @return array{stats: array, per_kecamatan: array, desa_per_kecamatan: array}
     */
    public function adminStats(): array
    {
        $stats = [
            'total_pemilih'   => DB::table('pemilihs')->count(),
            'total_kecamatan' => DB::table('kecamatans')->count(),
            'total_desa'      => DB::table('desas')->count(),
            'total_akun'      => DB::table('users')->count(),
        ];

        $perKecamatan = DB::table('kecamatans')
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($kec) {
                return [
                    'nama'  => $kec->nama,
                    'total' => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->count(),
                    'l'     => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->where('jenis_kelamin', 'L')->count(),
                    'p'     => DB::table('pemilihs')->where('kecamatan_id', $kec->id)->where('jenis_kelamin', 'P')->count(),
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
                'total'          => (int) $item->total,
            ]);

        return [
            'stats'              => $stats,
            'per_kecamatan'      => $perKecamatan,
            'desa_per_kecamatan' => $desaPerKecamatan,
        ];
    }

    /**
     * Get stats for Kecamatan dashboard (scoped to kecamatan).
     *
     * @param  string  $kecamatanId
     * @return array{kecamatan: string, stats: array, per_desa: array}
     */
    public function kecamatanStats(string $kecamatanId): array
    {
        $kecamatanNama = DB::table('kecamatans')->where('id', $kecamatanId)->value('nama');

        $totalPemilih = DB::table('pemilihs')->where('kecamatan_id', $kecamatanId)->count();
        $totalDesa    = DB::table('desas')->where('kecamatan_id', $kecamatanId)->count();

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

        return [
            'kecamatan' => $kecamatanNama,
            'stats'     => [
                'total_pemilih' => $totalPemilih,
                'total_desa'    => $totalDesa,
            ],
            'per_desa'  => $perDesa,
        ];
    }

    /**
     * Get stats for Desa dashboard (scoped to desa).
     *
     * @param  string  $desaId
     * @param  string  $kecamatanId
     * @return array{desa: string, kecamatan: string, stats: array}
     */
    public function desaStats(string $desaId, string $kecamatanId): array
    {
        $desaNama      = DB::table('desas')->where('id', $desaId)->value('nama');
        $kecamatanNama = DB::table('kecamatans')->where('id', $kecamatanId)->value('nama');

        $totalPemilih = DB::table('pemilihs')->where('desa_id', $desaId)->count();
        $lakiLaki     = DB::table('pemilihs')->where('desa_id', $desaId)->where('jenis_kelamin', 'L')->count();
        $perempuan    = DB::table('pemilihs')->where('desa_id', $desaId)->where('jenis_kelamin', 'P')->count();

        return [
            'desa'      => $desaNama,
            'kecamatan' => $kecamatanNama,
            'stats'     => [
                'total_pemilih' => $totalPemilih,
                'laki_laki'     => $lakiLaki,
                'perempuan'     => $perempuan,
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Desa;

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

        $desaId = $user->desa_id;
        $kecamatanId = $user->kecamatan_id;

        $desaNama = DB::table('desas')->where('id', $desaId)->value('nama');
        $kecamatanNama = DB::table('kecamatans')->where('id', $kecamatanId)->value('nama');

        $totalPemilih = DB::table('pemilihs')->where('desa_id', $desaId)->where('status', 'terverifikasi')->count();
        $lakiLaki = DB::table('pemilihs')->where('desa_id', $desaId)->where('jenis_kelamin', 'L')->where('status', 'terverifikasi')->count();
        $perempuan = DB::table('pemilihs')->where('desa_id', $desaId)->where('jenis_kelamin', 'P')->where('status', 'terverifikasi')->count();

        $kordes = AnggotaTim::query()
            ->where('desa_id', $desaId)
            ->where('role', 'kordes')
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('nama')
            ->values();

        $relawans = AnggotaTim::query()
            ->where('desa_id', $desaId)
            ->where('role', 'relawan')
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('nama')
            ->values();

        return Inertia::render('desa/Dashboard', [
            'desa' => $desaNama,
            'kecamatan' => $kecamatanNama,
            'kordes' => $kordes,
            'relawans' => $relawans,
            'stats' => [
                'total_pemilih' => $totalPemilih,
                'laki_laki' => $lakiLaki,
                'perempuan' => $perempuan,
            ],
        ]);
    }
}

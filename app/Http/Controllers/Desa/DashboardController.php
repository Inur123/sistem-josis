<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user   = $request->user();
        $desaId = $user->desa_id;

        $desaNama      = DB::table('desas')->where('id', $desaId)->value('nama');
        $kecamatanNama = DB::table('kecamatans')->where('id', $user->kecamatan_id)->value('nama');

        /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\Pemilih> $pemilihQuery */
        $pemilihQuery = Pemilih::query()->where('desa_id', $desaId);

        $totalPemilih = $pemilihQuery->count();
        $lakiLaki     = (clone $pemilihQuery)->where('jenis_kelamin', 'L')->count();
        $perempuan    = (clone $pemilihQuery)->where('jenis_kelamin', 'P')->count();

        return Inertia::render('desa/Dashboard', [
            'desa'      => $desaNama,
            'kecamatan' => $kecamatanNama,
            'stats'     => [
                'total_pemilih' => $totalPemilih,
                'laki_laki'     => $lakiLaki,
                'perempuan'     => $perempuan,
            ],
        ]);
    }
}

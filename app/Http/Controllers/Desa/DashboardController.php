<?php

namespace App\Http\Controllers\Desa;

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
        $desa = $user->desa;

        // Hitung statistik — jenis kelamin masih terenkripsi di DB,
        // query langsung tidak bisa, ambil dan decrypt di PHP
        $pemilihs = Pemilih::where('desa_id', $desa->id)
            ->select('jenis_kelamin')
            ->get();

        $totalPemilih = $pemilihs->count();
        $lakiLaki     = $pemilihs->filter(fn ($p) => strtoupper($p->jenis_kelamin) === 'L')->count();
        $perempuan    = $pemilihs->filter(fn ($p) => strtoupper($p->jenis_kelamin) === 'P')->count();

        return Inertia::render('desa/Dashboard', [
            'desa'      => $desa->nama,
            'kecamatan' => $user->kecamatan->nama,
            'stats' => [
                'total_pemilih' => $totalPemilih,
                'laki_laki'     => $lakiLaki,
                'perempuan'     => $perempuan,
            ],
        ]);
    }
}

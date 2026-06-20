<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $this->dashboard->kecamatanStats($user->kecamatan_id);

        return Inertia::render('kecamatan/Dashboard', [
            'kecamatan' => $data['kecamatan'],
            'stats'     => $data['stats'],
            'per_desa'  => $data['per_desa'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Desa;

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

        $data = $this->dashboard->desaStats($user->desa_id, $user->kecamatan_id);

        return Inertia::render('desa/Dashboard', [
            'desa'      => $data['desa'],
            'kecamatan' => $data['kecamatan'],
            'stats'     => $data['stats'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

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
        $data = $this->dashboard->adminStats();

        return Inertia::render('admin/Dashboard', [
            'stats'              => $data['stats'],
            'per_kecamatan'      => $data['per_kecamatan'],
            'desa_per_kecamatan' => $data['desa_per_kecamatan'],
        ]);
    }
}

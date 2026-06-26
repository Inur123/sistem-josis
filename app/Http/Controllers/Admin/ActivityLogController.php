<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Display a listing of the activity logs.
     * Jika request adalah AJAX (X-Requested-With), kembalikan JSON untuk pagination tanpa reload URL.
     */
    public function __invoke(Request $request): Response|JsonResponse
    {
        $page = max(1, $request->integer('page', 1));
        $perPage = self::PER_PAGE;

        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function (Activity $log): array {
                $causer = $log->causer;

                return [
                    'id' => $log->id,
                    'log_name' => $log->log_name,
                    'description' => $log->description,
                    'event' => $log->event,
                    'causer' => $causer instanceof User ? [
                        'name' => $causer->name,
                        'email' => $causer->email,
                        'role' => $causer->role,
                    ] : null,
                    'created_at' => $log->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                ];
            });

        // Jika AJAX fetch dari frontend (bukan Inertia navigation), kembalikan JSON saja.
        // Inertia juga mengirim X-Requested-With, tapi ia selalu mengirim X-Inertia juga.
        // Fetch biasa dari Vue TIDAK memiliki X-Inertia, sehingga ini aman dibedakan.
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' && ! $request->header('X-Inertia')) {
            return response()->json([
                'data' => $logs->items(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
            ]);
        }

        return Inertia::render('admin/ActivityLog', [
            'logs' => $logs,
        ]);
    }
}

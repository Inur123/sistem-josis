<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function __invoke(Request $request): Response
    {
        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($log) => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'event' => $log->event,
                'causer' => $log->causer ? [
                    'name' => $log->causer->name,
                    'email' => $log->causer->email,
                    'role' => $log->causer->role,
                ] : null,
                'created_at' => $log->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
            ]);

        return Inertia::render('admin/ActivityLog', [
            'logs' => $logs,
        ]);
    }
}

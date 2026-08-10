<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'admin']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $stats = [
            'total'       => ActivityLog::count(),
            'logins_today' => ActivityLog::where('action', 'login')->whereDate('created_at', today())->count(),
            'failed'      => ActivityLog::where('action', 'login')->where('properties->success', false)->count(),
            'actions_today' => ActivityLog::whereDate('created_at', today())->count(),
        ];

        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'stats', 'actions'));
    }

    public function show(ActivityLog $log)
    {
        $log->load(['user', 'admin']);
        return view('admin.audit-logs.show', compact('log'));
    }

    public function export(Request $request)
    {
        $query = ActivityLog::with(['user', 'admin']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->limit(10000)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-logs-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Action', 'Description', 'IP Address', 'User Agent']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->email ?? $log->admin?->email ?? 'System',
                    $log->action,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function clear(Request $request)
    {
        $request->validate(['days' => 'required|integer|min:1']);

        $cutoff = now()->subDays($request->days);
        $count = ActivityLog::where('created_at', '<', $cutoff)->count();
        ActivityLog::where('created_at', '<', $cutoff)->delete();

        return back()->with('success', "Cleared {$count} log entries older than {$request->days} days.");
    }
}

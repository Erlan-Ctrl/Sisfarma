<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $action = trim((string) $request->query('action', ''));
        $userId = (int) $request->integer('user_id', 0);

        $logs = AuditLog::query()
            ->with('user')
            ->when($action !== '', fn ($qr) => $qr->where('action', $action))
            ->when($userId > 0, fn ($qr) => $qr->where('user_id', $userId))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->where('action', 'ilike', "%{$q}%")
                        ->orWhere('auditable_type', 'ilike', "%{$q}%");

                    if (ctype_digit($q)) {
                        $sub->orWhere('auditable_id', (int) $q)
                            ->orWhere('user_id', (int) $q);
                    }
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->limit(200)
            ->pluck('action')
            ->all();

        return view('admin.audit.index', [
            'logs' => $logs,
            'q' => $q,
            'action' => $action,
            'actions' => $actions,
            'userId' => $userId,
        ]);
    }

    public function show(AuditLog $log)
    {
        $log->load('user');

        return view('admin.audit.show', [
            'log' => $log,
        ]);
    }
}

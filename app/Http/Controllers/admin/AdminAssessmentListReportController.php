<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAssessmentListReportController extends Controller
{
    public function index(Request $request): View
    {
        $preStatus = $request->get('status', '');
        return view('admin.reports.assessment-list.index', compact('preStatus'));
    }

    public function ajax(Request $request): JsonResponse
    {
        $table = (new Assessment)->getTable();

        $query = Assessment::with('project:id,project_name')
            ->select($table . '.*');

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhereHas('project', fn ($p) => $p->where('project_name', 'like', "%{$kw}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('submitted_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('submitted_at');
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate($table . '.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate($table . '.created_at', '<=', $request->end_date);
        }

        $total = $query->count();

        $start  = max(0, (int) $request->get('start', 0));
        $length = max(1, (int) $request->get('length', 10));

        $assessments = $query->orderByDesc($table . '.created_at')
            ->offset($start)
            ->limit($length)
            ->get();

        $rows = $assessments->map(function ($a, $i) use ($start) {
            $isCompleted = !is_null($a->submitted_at);
            $badge = $isCompleted
                ? '<span class="badge bg-success-subtle text-success fw-medium">Completed</span>'
                : '<span class="badge bg-warning-subtle text-warning fw-medium">Pending</span>';

            return [
                'DT_RowIndex'     => $start + $i + 1,
                'project_name'    => e($a->project?->project_name ?? '—'),
                'assessment_name' => e($a->name),
                'created_at'      => $a->created_at?->format('d M Y') ?? '—',
                'submitted_at'    => $a->submitted_at?->format('d M Y') ?? '—',
                'status'          => $badge,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->get('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }
}

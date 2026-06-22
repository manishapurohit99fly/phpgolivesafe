<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\ChecklistCategory;

class AssessmentReportService
{
    public function getReportData(Assessment $assessment): array
    {
        $assessment->loadMissing(['project', 'users', 'sharedReports']);

        $checklists = AssessmentChecklist::where('assessment_id', $assessment->id)
            ->with(['checklistItem.category', 'checkedBy'])
            ->get();

        $total     = $checklists->count();
        $completed = $checklists->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $grouped = $checklists
            ->filter(fn ($ac) => $ac->checklistItem?->category_id !== null)
            ->groupBy(fn ($ac) => $ac->checklistItem->category_id);

        $categoryIds = $grouped->keys();

        $categories = ChecklistCategory::whereIn('id', $categoryIds)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) use ($grouped) {
                $items      = $grouped->get($cat->id, collect())
                    ->sortBy(fn ($ac) => $ac->checklistItem->sort_order ?? 0)
                    ->values();
                $catTotal   = $items->count();
                $catDone    = $items->where('is_checked', 1)->count();
                $catPercent = $catTotal > 0 ? (int) round(($catDone / $catTotal) * 100) : 0;

                return [
                    'category' => $cat,
                    'items'    => $items,
                    'total'    => $catTotal,
                    'completed'=> $catDone,
                    'pending'  => $catTotal - $catDone,
                    'percent'  => $catPercent,
                ];
            })
            ->values();

        $verifiedUsers = $checklists
            ->where('is_checked', 1)
            ->whereNotNull('checked_by')
            ->pluck('checkedBy')
            ->filter()
            ->unique('id')
            ->values();

        $lastVerifiedAt = $checklists
            ->where('is_checked', 1)
            ->whereNotNull('checked_at')
            ->sortByDesc('checked_at')
            ->first()?->checked_at;

        return [
            'assessment'     => $assessment,
            'project'        => $assessment->project,
            'total'          => $total,
            'completed'      => $completed,
            'pending'        => $pending,
            'percent'        => $percent,
            'categories'     => $categories,
            'verifiedUsers'  => $verifiedUsers,
            'lastVerifiedAt' => $lastVerifiedAt,
            'deploymentNotes'=> $assessment->description,
            'assignedUsers'  => $assessment->users,
            'shareToken'     => $assessment->sharedReports->first()?->unique_token,
        ];
    }
}

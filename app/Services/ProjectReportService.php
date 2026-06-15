<?php

namespace App\Services;

use App\Models\ChecklistCategory;
use App\Models\Project;
use App\Models\ProjectChecklist;

class ProjectReportService
{
    /**
     * Build the full report data array for a given project.
     * Used by admin, user, and public report views — single source of truth.
     */
    public function getReportData(Project $project): array
    {
        $project->loadMissing(['users', 'sharedReports']);

        $checklists = ProjectChecklist::where('project_id', $project->id)
            ->with(['checklistItem.category', 'checkedBy'])
            ->get();

        $total     = $checklists->count();
        $completed = $checklists->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Group items by category_id, skipping orphaned rows
        $grouped = $checklists
            ->filter(fn ($pc) => $pc->checklistItem?->category_id !== null)
            ->groupBy(fn ($pc) => $pc->checklistItem->category_id);

        $categoryIds = $grouped->keys();

        $categories = ChecklistCategory::whereIn('id', $categoryIds)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) use ($grouped) {
                $items      = $grouped->get($cat->id, collect())
                    ->sortBy(fn ($pc) => $pc->checklistItem->sort_order ?? 0)
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

        // Unique users who verified at least one item
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
            'project'        => $project,
            'total'          => $total,
            'completed'      => $completed,
            'pending'        => $pending,
            'percent'        => $percent,
            'categories'     => $categories,
            'verifiedUsers'  => $verifiedUsers,
            'lastVerifiedAt' => $lastVerifiedAt,
            'deploymentNotes'=> $project->deployment_notes,
            'assignedUsers'  => $project->users,
            'shareToken'     => $project->sharedReports->first()?->unique_token,
        ];
    }
}

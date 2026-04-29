<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 20), [10, 20, 50, 100])
            ? (int) $request->input('per_page', 20) : 20;

        $query = Activity::with('causer')->latest();

        if ($request->filled('user_id')) {
            $query->where('causer_type', \App\Models\User::class)
                  ->where('causer_id', $request->input('user_id'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate($perPage)->withQueryString();

        // Distinct values for filter dropdowns
        $subjectTypes = Activity::distinct()->pluck('subject_type')->filter()->sort()->values();
        $users        = User::orderBy('name')->get(['id', 'name']);
        $events       = Activity::distinct()->pluck('event')->filter()->sort()->values();

        return view('admin.activity-logs.index',
            compact('logs', 'subjectTypes', 'users', 'events', 'perPage'));
    }
}


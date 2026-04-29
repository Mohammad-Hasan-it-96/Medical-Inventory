@extends('layouts.app')
@section('title', __('admin.activity_log_title'))
@section('content')
<div class="container-fluid">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-clock-history me-2"></i>{{ __('admin.activity_log_title') }}</h3>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                {{-- User --}}
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.activity_log_user') }}</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">{{ __('admin.activity_log_all_users') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject type --}}
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.activity_log_subject') }}</label>
                    <select name="subject_type" class="form-select form-select-sm">
                        <option value="">{{ __('admin.activity_log_all_types') }}</option>
                        @foreach($subjectTypes as $type)
                            <option value="{{ $type }}" {{ request('subject_type') === $type ? 'selected' : '' }}>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Event --}}
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.activity_log_event') }}</label>
                    <select name="event" class="form-select form-select-sm">
                        <option value="">{{ __('admin.activity_log_all_events') }}</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>
                                {{ __('admin.activity_event.' . $ev, [], null) ?? ucfirst($ev) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date from --}}
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>

                {{-- Date to --}}
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">{{ __('admin.activity_log_user') }}</th>
                            <th>{{ __('admin.activity_log_event') }}</th>
                            <th>{{ __('admin.activity_log_subject') }}</th>
                            <th>{{ __('admin.activity_log_desc') }}</th>
                            <th class="text-end pe-4">{{ __('admin.activity_log_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            {{-- User --}}
                            <td class="ps-4">
                                @if($log->causer)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                             style="width:30px;height:30px;font-size:.75rem;flex-shrink:0">
                                            {{ strtoupper(substr($log->causer->name, 0, 1)) }}
                                        </div>
                                        <span class="small fw-medium">{{ $log->causer->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small"><i class="bi bi-gear me-1"></i>{{ __('admin.activity_log_system') }}</span>
                                @endif
                            </td>

                            {{-- Event badge --}}
                            <td>
                                @php
                                    $eventColors = [
                                        'created'       => 'success',
                                        'updated'       => 'primary',
                                        'price_changed' => 'warning',
                                        'deleted'       => 'danger',
                                        'confirmed'     => 'success',
                                        'cancelled'     => 'danger',
                                        'adjusted'      => 'info',
                                    ];
                                    $color = $eventColors[$log->event ?? ''] ?? 'secondary';
                                    $label = __('admin.activity_event.' . ($log->event ?? ''), [], null)
                                             ?? ucfirst($log->event ?? '—');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            </td>

                            {{-- Subject --}}
                            <td>
                                @if($log->subject_type)
                                    <span class="badge bg-light text-dark border">
                                        {{ class_basename($log->subject_type) }}
                                        @if($log->subject_id)
                                            #{{ $log->subject_id }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Description --}}
                            <td class="text-muted small" style="max-width:380px">
                                {{ $log->description }}
                            </td>

                            {{-- Date --}}
                            <td class="text-end pe-4 text-muted small text-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                                {{ __('admin.activity_log_empty') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer: per-page + pagination --}}
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except(['page','per_page']) as $k => $v)
                    @if($v !== '' && $v !== null)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach
                <label class="text-muted small mb-0">{{ __('admin.per_page') }}</label>
                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            @if($logs->hasPages())
                <div>{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection


@php
    $visibleTypes = match ($audience) {
        'admin' => ['progress', 'customer_report', 'driver_report', 'driver_assessment', 'admin_feedback'],
        'driver' => ['progress', 'driver_report', 'driver_assessment', 'admin_feedback'],
        default => ['progress', 'customer_report', 'admin_feedback'],
    };
@endphp
<div class="mt-3">
    <h3 class="h6">Assignment updates and feedback</h3>
    @forelse($activities->whereIn('entry_type', $visibleTypes) as $activity)
        <div class="border rounded p-3 mb-2 bg-light">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <strong>{{ ucfirst(str_replace('_', ' ', $activity->entry_type)) }}</strong>
                <small class="text-muted">{{ $activity->created_at->format('M j, Y g:i A') }} · {{ $activity->user?->name ?? 'System' }}</small>
            </div>
            @if($activity->status)
                <div class="small mt-1"><strong>Progress:</strong> {{ ucfirst(str_replace('_', ' ', $activity->status)) }}</div>
            @endif
            @if($activity->rating)
                <div class="small mt-1"><strong>Rating:</strong> {{ $activity->rating }}/5</div>
            @endif
            <p class="mb-0 mt-2" style="white-space: pre-wrap">{{ $activity->message }}</p>
        </div>
    @empty
        <p class="text-muted small mb-0">No updates or feedback have been recorded.</p>
    @endforelse
</div>

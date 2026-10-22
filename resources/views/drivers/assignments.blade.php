@extends('layouts.dashboard')

@section('title', 'My Assignments')

@section('content')
<div class="container-max content-section">
    <div class="mb-4">
        <h2>My Assignments</h2>
        <p class="text-muted">Track approved work, post progress updates, and send private reports to the admin team.</p>
    </div>

    @foreach([['items' => $serviceRequests, 'type' => 'service-request'], ['items' => $bookings, 'type' => 'booking']] as $group)
        @foreach($group['items'] as $assignment)
            @php
                $customer = $assignment->user;
                $serviceRequest = $group['type'] === 'service-request';
                $latestProgress = $assignment->assignmentActivities->firstWhere('entry_type', 'progress');
            @endphp
            <section class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <h3 class="h5">{{ $serviceRequest ? 'Service request' : 'Booking' }} #{{ $assignment->getKey() }}</h3>
                        @if($latestProgress?->status)
                            <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $latestProgress->status)) }}</span>
                        @else
                            <span class="badge bg-secondary">No progress update</span>
                        @endif
                    </div>
                    <p class="mb-1"><strong>Assigned to:</strong> {{ $customer->name }} · {{ $customer->email }}</p>
                    @if($serviceRequest)
                        <p class="mb-1">{{ $assignment->service_label }} · {{ $assignment->location }}</p>
                        <p class="mb-1"><strong>Start date:</strong> {{ $assignment->preferred_start_date?->format('M j, Y') ?? 'Not specified' }} · {{ $assignment->duration ?: 'Duration not specified' }}</p>
                    @else
                        <p class="mb-1">{{ ucfirst($assignment->booking_type) }} · {{ $assignment->location ?: 'Location not specified' }}</p>
                        <p class="mb-1"><strong>Booking date:</strong> {{ $assignment->booking_date?->format('M j, Y g:i A') ?? 'Not specified' }}</p>
                    @endif
                    @include('assignments._timeline', ['activities' => $assignment->assignmentActivities, 'audience' => 'driver'])

                    <div class="row g-3 mt-2">
                        <div class="col-lg-6">
                            <form method="POST" action="{{ route('assignments.activity', ['type' => $group['type'], 'id' => $assignment->getKey()]) }}" class="border rounded p-3">
                                @csrf
                                <input type="hidden" name="entry_type" value="progress">
                                <h4 class="h6">Update assignment progress</h4>
                                <label class="form-label">Status</label>
                                <select class="form-select mb-2" name="status" required>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <label class="form-label">Progress notes</label>
                                <textarea class="form-control mb-2" name="message" rows="3" minlength="3" maxlength="3000" required></textarea>
                                <button class="btn btn-primary" type="submit">Save progress</button>
                            </form>
                        </div>
                        <div class="col-lg-6">
                            <form method="POST" action="{{ route('assignments.activity', ['type' => $group['type'], 'id' => $assignment->getKey()]) }}" class="border rounded p-3">
                                @csrf
                                <input type="hidden" name="entry_type" value="driver_report">
                                <h4 class="h6">Privately report a customer issue</h4>
                                <label class="form-label">Report details</label>
                                <textarea class="form-control mb-2" name="message" rows="5" minlength="3" maxlength="3000" required></textarea>
                                <p class="small text-muted">Only administrators can see this report. They can send feedback to the assignment participants.</p>
                                <button class="btn btn-outline-danger" type="submit">Send private report to admin</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
        {{ $group['items']->links() }}
    @endforeach

    @if($serviceRequests->isEmpty() && $bookings->isEmpty())
        <div class="alert alert-info">You do not have any active driver assignments yet.</div>
    @endif
</div>
@endsection

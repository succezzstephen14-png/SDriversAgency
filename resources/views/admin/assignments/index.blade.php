@extends('layouts.admin')

@section('title', 'Assignments and Feedback')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3">Assignments, progress and feedback</h1>
        <p class="text-muted">Review assigned drivers and vehicles, follow progress, assess drivers, and respond to private reports.</p>
    </div>

    @foreach([['items' => $serviceRequests, 'type' => 'service-request', 'heading' => 'Service requests'], ['items' => $bookings, 'type' => 'booking', 'heading' => 'Bookings']] as $group)
        <section class="mb-5">
            <h2 class="h4 mb-3">{{ $group['heading'] }}</h2>
            @forelse($group['items'] as $assignment)
                @php
                    $isServiceRequest = $group['type'] === 'service-request';
                    $driver = $isServiceRequest ? $assignment->assignedDriver : $assignment->driver;
                    $vehicle = $isServiceRequest ? $assignment->assignedVehicle : $assignment->vehicle;
                    $latestProgress = $assignment->assignmentActivities->firstWhere('entry_type', 'progress');
                    $customerName = $isServiceRequest ? $assignment->user?->name : ($assignment->guest_name ?: $assignment->user?->name);
                    $customerEmail = $isServiceRequest ? $assignment->user?->email : ($assignment->guest_email ?: $assignment->user?->email);
                    $customerPhone = $isServiceRequest ? $assignment->user?->phone : ($assignment->guest_phone ?: $assignment->user?->phone);
                @endphp
                <article class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <h3 class="h5 mb-1">{{ $isServiceRequest ? 'Service request' : 'Booking' }} #{{ $assignment->getKey() }}</h3>
                            <span class="badge bg-primary">{{ $latestProgress?->status ? ucfirst(str_replace('_', ' ', $latestProgress->status)) : 'No progress update' }}</span>
                        </div>
                        <div class="row mt-2">
                            <div class="col-lg-6">
                                <p class="mb-1"><strong>Customer:</strong> {{ $customerName ?? 'Unknown' }} · {{ $customerEmail ?? 'No email' }}</p>
                                <p class="mb-1"><strong>Customer phone:</strong>
                                    @if($customerPhone)
                                        <a href="tel:{{ $customerPhone }}">{{ $customerPhone }}</a>
                                    @else
                                        Not provided
                                    @endif
                                </p>
                                <p class="mb-1"><strong>Assignment status:</strong> {{ str_replace('_', ' ', ucfirst($assignment->status)) }}</p>
                                @if($isServiceRequest)
                                    <p class="mb-1"><strong>Request:</strong> {{ ucfirst($assignment->request_type) }} · {{ $assignment->service_label }} · {{ $assignment->location }}</p>
                                @else
                                    <p class="mb-1"><strong>Booking:</strong> {{ ucfirst($assignment->booking_type) }} · {{ $assignment->location ?: 'Location not specified' }} · {{ $assignment->booking_date?->format('M j, Y') ?? 'Date not specified' }}</p>
                                @endif
                                <p class="mb-1"><strong>Assigned driver:</strong> {{ $driver?->user?->name ?? 'None' }} @if($driver) ({{ $driver->id }}) @endif</p>
                                <p class="mb-1"><strong>Assigned vehicle:</strong> {{ $vehicle ? $vehicle->year . ' ' . $vehicle->make . ' ' . $vehicle->model . ' · ' . $vehicle->license_plate : 'None' }}</p>
                            </div>
                            <div class="col-lg-6">
                                @include('assignments._timeline', ['activities' => $assignment->assignmentActivities, 'audience' => 'admin'])
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-lg-4">
                                <form method="POST" action="{{ route('admin.assignments.activity', ['type' => $group['type'], 'id' => $assignment->getKey()]) }}" class="border rounded p-3 h-100">
                                    @csrf
                                    <input type="hidden" name="entry_type" value="progress">
                                    <h4 class="h6">Record progress</h4>
                                    <select class="form-select mb-2" name="status" required>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="in_progress">In progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                    <textarea class="form-control mb-2" name="message" rows="3" minlength="3" maxlength="3000" placeholder="Progress note" required></textarea>
                                    <button class="btn btn-outline-primary" type="submit">Save progress</button>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <form method="POST" action="{{ route('admin.assignments.activity', ['type' => $group['type'], 'id' => $assignment->getKey()]) }}" class="border rounded p-3 h-100">
                                    @csrf
                                    <input type="hidden" name="entry_type" value="admin_feedback">
                                    <h4 class="h6">Send feedback to participants</h4>
                                    <textarea class="form-control mb-2" name="message" rows="4" minlength="3" maxlength="3000" placeholder="Feedback visible to the customer and assigned driver" required></textarea>
                                    <button class="btn btn-primary" type="submit">Send feedback</button>
                                </form>
                            </div>
                            @if($driver)
                                <div class="col-lg-4">
                                    <form method="POST" action="{{ route('admin.assignments.activity', ['type' => $group['type'], 'id' => $assignment->getKey()]) }}" class="border rounded p-3 h-100">
                                        @csrf
                                        <input type="hidden" name="entry_type" value="driver_assessment">
                                        <h4 class="h6">Practical driver assessment</h4>
                                        <label class="form-label">Score</label>
                                        <select class="form-select mb-2" name="rating" required>
                                            <option value="">Select score</option>
                                            @for($score = 5; $score >= 1; $score--)
                                                <option value="{{ $score }}">{{ $score }} / 5</option>
                                            @endfor
                                        </select>
                                        <textarea class="form-control mb-2" name="message" rows="3" minlength="3" maxlength="3000" placeholder="Assessment observations" required></textarea>
                                        <button class="btn btn-outline-success" type="submit">Save assessment</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="alert alert-light border">No assigned {{ strtolower($group['heading']) }} found.</div>
            @endforelse
            {{ $group['items']->links() }}
        </section>
    @endforeach
</div>
@endsection

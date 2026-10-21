@extends('layouts.admin')

@section('title', 'Service Requests')

@section('content')
<div class="admin-wrapper">
    <div class="container-fluid">
        <div class="admin-header">
            <div><h2>Service customer requests</h2><p class="text-muted mb-0">Review driver and vehicle requests, assign approved resources, and send a quote.</p></div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Request</th><th>Customer</th><th>Type / Service</th><th>Location</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                    @forelse($requests as $serviceRequest)
                        <tr>
                            <td>#{{ $serviceRequest->id }}</td>
                            <td>{{ $serviceRequest->user->name }}<div class="small text-muted">{{ $serviceRequest->user->email }}</div></td>
                            <td>{{ ucfirst($serviceRequest->request_type) }}<div class="small text-muted">{{ $serviceRequest->service_label }}{{ $serviceRequest->vehicle_type ? ' · ' . ucfirst($serviceRequest->vehicle_type) : '' }}</div></td>
                            <td>{{ $serviceRequest->location }}</td>
                            <td><span class="badge text-bg-{{ $serviceRequest->status === 'rejected' ? 'danger' : ($serviceRequest->status === 'paid' ? 'success' : 'primary') }}">{{ str_replace('_', ' ', ucfirst($serviceRequest->status)) }}</span></td>
                            <td>{{ $serviceRequest->created_at->format('M j, Y') }}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.service-requests.show', $serviceRequest) }}">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No requests have been submitted.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection

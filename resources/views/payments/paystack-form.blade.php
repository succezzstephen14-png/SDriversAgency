@extends('layouts.app')

@section('title', $title ?? 'Paystack Payment')

@section('content')
<div class="container-max my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">
                        <i class="fas fa-lock text-primary me-2"></i>{{ $title ?? 'Complete Payment' }}
                    </h3>

                    <div class="alert alert-info">
                        <strong>Payment Amount:</strong> {{ config('app.currency_label', 'GHC') }} {{ number_format($payment->amount / 100, 2) }}
                    </div>

                    @if(!empty($checkoutError))
                        <div class="alert alert-danger" role="alert">{{ $checkoutError }}</div>
                    @endif

                    <div class="mb-4">
                        <p class="mb-1"><strong>Customer:</strong> {{ Auth::check() ? Auth::user()->name : ($payment->data['guest_name'] ?? 'Guest User') }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ Auth::check() ? Auth::user()->email : ($payment->data['guest_email'] ?? 'N/A') }}</p>
                        <p class="mb-1"><strong>Reference:</strong> {{ $payment->reference }}</p>

                        @if(isset($payment->data['service_request_id']) && isset($serviceRequest))
                            <p class="mb-1"><strong>Request:</strong> #{{ $serviceRequest->id }} · {{ ucfirst($serviceRequest->request_type) }}</p>
                            <p class="mb-1"><strong>Service:</strong> {{ $serviceRequest->service_label }}</p>
                            <p class="mb-1"><strong>Location:</strong> {{ $serviceRequest->location }}</p>
                        @elseif(isset($payment->data['booking_type']))
                            <p class="mb-1"><strong>Booking Type:</strong> {{ ucfirst($payment->data['booking_type']) }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ $payment->data['booking_date'] }}</p>
                            <p class="mb-1"><strong>Time:</strong> {{ $payment->data['start_time'] }} - {{ $payment->data['end_time'] }}</p>
                            <p class="mb-1"><strong>Location:</strong> {{ $payment->data['location'] }}</p>
                            @if(isset($payment->data['guest_name']))
                                <p class="mb-1"><strong>Guest:</strong> {{ $payment->data['guest_name'] }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $payment->data['guest_email'] }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $payment->data['guest_phone'] }}</p>
                            @endif
                        @elseif($payment->type === 'driver')
                            <p class="mb-1"><strong>License:</strong> {{ $payment->data['license_number'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Experience:</strong> {{ $payment->data['experience_years'] ?? 'N/A' }} years</p>
                        @elseif($payment->type === 'vehicle')
                            <p class="mb-1"><strong>Vehicle:</strong> {{ $payment->data['make'] ?? 'N/A' }} {{ $payment->data['model'] ?? '' }}</p>
                            <p class="mb-1"><strong>Type:</strong> {{ ucfirst($payment->data['vehicle_type'] ?? 'N/A') }}</p>
                        @elseif($payment->type === 'recruitment')
                            <p class="mb-1"><strong>Role:</strong> {{ $payment->data['title'] ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Position Type:</strong> {{ ucfirst(str_replace('_', ' ', $payment->data['position_type'] ?? 'N/A')) }}</p>
                        @endif
                    </div>

                    <form id="paymentForm" method="POST" action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('payment.checkout', now()->addMinutes(30), ['payment' => $payment->getKey()]) }}">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                            id="paystackBtn"
                        >
                            <i class="fas fa-credit-card me-2"></i>Continue to Paystack
                        </button>

                        <a href="{{ isset($serviceRequest) ? route('service.requests.index') : url()->previous() }}" class="btn btn-secondary btn-lg w-100 mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                    </form>

                    <div class="alert alert-light mt-4">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Payments are securely processed by Paystack.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paystackBtn = document.getElementById('paystackBtn');
    const paymentForm = document.getElementById('paymentForm');

    if (paystackBtn && paymentForm) {
        paymentForm.addEventListener('submit', function() {
            paystackBtn.disabled = true;
            paystackBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connecting to Paystack...';
        });
    }
});
</script>
@endsection

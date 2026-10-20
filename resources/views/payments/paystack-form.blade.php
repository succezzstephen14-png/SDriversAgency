@extends('layouts.app')

@section('title', $title ?? 'Paystack Payment')

@php
    $paystackPublicKey = config('services.paystack.public_key');
    $paymentEmail = Auth::check() ? Auth::user()->email : ($payment->data['guest_email'] ?? 'guest@example.com');
@endphp

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

                    <div class="mb-4">
                        <p class="mb-1"><strong>Customer:</strong> {{ Auth::check() ? Auth::user()->name : ($payment->data['guest_name'] ?? 'Guest User') }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ Auth::check() ? Auth::user()->email : ($payment->data['guest_email'] ?? 'N/A') }}</p>
                        <p class="mb-1"><strong>Reference:</strong> {{ $payment->reference }}</p>

                        @if(isset($payment->data['booking_type']))
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

                    <form id="paymentForm" method="POST">
                        @csrf
                        @if($paystackPublicKey)
                        <button
                            type="button"
                            class="btn btn-primary btn-lg w-100"
                            id="paystackBtn"
                            onclick="payWithPaystack()"
                        >
                            <i class="fas fa-credit-card me-2"></i>Pay with Paystack
                        </button>
                        @else
                            <div class="alert alert-danger" role="alert">
                                Online payment is temporarily unavailable. Please contact support.
                            </div>
                        @endif

                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg w-100 mt-2">
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
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
const paystackConfig = {
    key: @json($paystackPublicKey),
    email: @json($paymentEmail),
    amount: @json($amount),
    reference: @json($payment->reference),
    callbackUrl: @json(route('payment.success'))
};

function payWithPaystack() {
    const paystackBtn = document.getElementById('paystackBtn');
    if (!paystackBtn || !paystackConfig.key || typeof PaystackPop === 'undefined') {
        alert('Online payment is currently unavailable. Please contact support.');
        return;
    }

    const originalText = paystackBtn.innerHTML;

    paystackBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    paystackBtn.disabled = true;

    try {
        const handler = PaystackPop.setup({
            key: paystackConfig.key,
            email: paystackConfig.email,
            amount: paystackConfig.amount,
            ref: paystackConfig.reference,
            currency: 'GHS',
            onClose: function() {
                paystackBtn.innerHTML = originalText;
                paystackBtn.disabled = false;
                alert('Payment cancelled.');
            },
            callback: function(response) {
                if (response.status === 'success') {
                    paystackBtn.innerHTML = '<i class="fas fa-check me-2"></i>Payment Successful!';
                    paystackBtn.className = 'btn btn-success btn-lg w-100';
                    window.location.href = paystackConfig.callbackUrl + '?reference=' + encodeURIComponent(response.reference);
                    return;
                }

                paystackBtn.innerHTML = originalText;
                paystackBtn.disabled = false;
                alert('Payment failed. Please try again.');
            }
        });
        handler.openIframe();
    } catch (error) {
        console.error('Paystack setup error:', error);
        paystackBtn.innerHTML = originalText;
        paystackBtn.disabled = false;
        alert('Payment setup failed. Please refresh the page and try again.');
    }
}

// Check if Paystack is loaded
document.addEventListener('DOMContentLoaded', function() {
    const paystackBtn = document.getElementById('paystackBtn');
    if (paystackBtn && typeof PaystackPop === 'undefined') {
        console.error('Paystack library not loaded');
        paystackBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Payment Unavailable';
        paystackBtn.disabled = true;
    }
});
</script>
@endsection

@include('layouts.header')

@php
    $expires   = now()->addDay();
    $statusUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('order.payment.status', $expires, ['orderNumber' => $order->order_number]);
    $retryUrl  = \Illuminate\Support\Facades\URL::temporarySignedRoute('order.payment.retry', $expires, ['orderNumber' => $order->order_number]);
    $trackUrl  = route('order.track', ['order_number' => $order->order_number, 'phone' => $order->customer_phone]);
    $paid      = $order->isPaid();
    $pending   = $order->payment_status === 'pending' && $payment && $payment->isPending();
    $canceled  = $order->status === 'canceled';
    $canRetry  = $order->canRetryPayment();
@endphp

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Payment</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active text-white">Payment</li>
    </ol>
</div>
<!-- Single Page Header End -->

<div class="container-fluid">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-7">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="border rounded p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Order {{ $order->order_number }}</h4>
                        <span class="badge {{ $order->payment_badge }}">{{ $order->payment_label }}</span>
                    </div>

                    <p class="mb-1"><strong>Amount to pay:</strong> {{ number_format($order->total_amount) }} UGX</p>
                    <p class="mb-4"><strong>Placed:</strong> {{ $order->created_at->format('d M Y, H:i') }}</p>

                    @if ($paid)
                        <div class="alert alert-success">
                            <h5 class="alert-heading mb-1"><i class="fas fa-check-circle me-1"></i> Payment received</h5>
                            Thank you! Your order is confirmed and is now being processed.
                            @if ($order->customer_email)
                                A confirmation has been sent to {{ $order->customer_email }}.
                            @endif
                        </div>
                        <a href="{{ $trackUrl }}" class="btn btn-primary rounded-pill px-4 text-white">Track Your Order</a>
                    @elseif ($canceled)
                        <div class="alert alert-secondary mb-0">This order was cancelled, so no payment is needed.</div>
                    @elseif ($pending)
                        <div class="alert alert-warning">
                            <h5 class="alert-heading mb-1">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Waiting for your approval
                            </h5>
                            A payment prompt for <strong>{{ number_format($payment->amount) }} UGX</strong> was sent to
                            <strong>{{ $payment->phone }}</strong>. Check your phone and enter your Mobile Money PIN to
                            approve it. This page updates automatically.
                        </div>
                        <p class="text-muted small mb-0">No prompt? Dial your MTN (*165#) or Airtel (*185#) menu and
                            look for pending approvals, or wait a moment and retry below.</p>
                    @else
                        <div class="alert alert-danger">
                            <h5 class="alert-heading mb-1">Payment not completed</h5>
                            {{ $payment?->failure_reason ?: 'We have not received your payment yet.' }}
                        </div>
                    @endif

                    @if (! $paid && ! $canceled && $canRetry)
                        <form action="{{ $retryUrl }}" method="POST" class="mt-4" id="retry-payment-form">
                            @csrf
                            <label class="form-label">Mobile Money number (MTN / Airtel)</label>
                            <div class="input-group mb-2">
                                <input type="tel" name="payment_phone" class="form-control"
                                    value="{{ old('payment_phone', $payment->phone ?? app(\App\Services\MarzPayService::class)->normalizePhone($order->customer_phone)) }}"
                                    placeholder="+256772123456" pattern="\+[0-9]{9,15}"
                                    title="Include the country code, e.g. +256772123456" required>
                                <button type="submit" class="btn btn-primary text-white" id="retry-payment-btn"
                                    data-label="{{ $payment ? 'Retry Payment' : 'Pay Now' }}">
                                    <span class="btn-label">{{ $payment ? 'Retry Payment' : 'Pay Now' }}</span>
                                    <span class="btn-busy d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Sending prompt…
                                    </span>
                                </button>
                            </div>
                            @error('payment_phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small">Start with the country code, e.g. +256 for Uganda.</div>
                        </form>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@if (! $paid && ! $canceled && $canRetry)
    <script>
        // Retry / Pay Now: lock the button as soon as it is clicked so the customer
        // cannot fire several payment prompts by clicking again and again.
        (function() {
            const form = document.getElementById('retry-payment-form');
            const btn  = document.getElementById('retry-payment-btn');
            if (!form || !btn) return;

            const setBusy = function(busy) {
                btn.disabled = busy;
                btn.setAttribute('aria-busy', busy ? 'true' : 'false');
                btn.querySelector('.btn-label').classList.toggle('d-none', busy);
                btn.querySelector('.btn-busy').classList.toggle('d-none', !busy);
                const phone = form.querySelector('input[name="payment_phone"]');
                if (phone) phone.readOnly = busy; // readOnly (not disabled) so the value is still submitted
            };

            form.addEventListener('submit', function(e) {
                if (form.dataset.submitting === '1') {
                    e.preventDefault(); // already sending, ignore repeat clicks / Enter presses
                    return;
                }
                if (!form.checkValidity()) return; // let the browser show its own validation message
                form.dataset.submitting = '1';
                setBusy(true);
            });

            // Coming back with the browser Back button must not leave a stuck spinner.
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    form.dataset.submitting = '0';
                    setBusy(false);
                }
            });
        })();
    </script>
@endif

@if (! $paid && ! $canceled && $pending)
    <script>
        (function() {
            const statusUrl = @json($statusUrl);
            const retryAlreadyShown = @json($canRetry);
            const timer = setInterval(async function() {
                try {
                    const res = await fetch(statusUrl, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.payment_status !== 'pending' || (data.can_retry && !retryAlreadyShown)) {
                        clearInterval(timer);
                        window.location.reload();
                    }
                } catch (e) {
                    /* ignore, try again on the next tick */
                }
            }, 5000);
        })();
    </script>
@endif

@include('layouts.footer')

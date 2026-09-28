@include('layouts.header')

<!-- Hero Start -->
<div class="container-fluid py-5 mb-5 hero-header">
    <div class="container py-5">

    </div>
</div>
<!-- Hero End -->

<?php
use App\Http\Controllers\Helper;
?>


<style>
    .btn-minus,
    .btn-plus {
        width: 30px;
        height: 30px;
        padding: 0;
    }

    .btn-handle {
        width: 30px;
        height: 30px;
        padding: 0;
    }
</style>

<div class="container-fluid vesitable">
    <div class="container py-5">
        <h1 class="mb-3">My Orders</h1>
        <div class="table-responsive">

            @forelse($orders as $order)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <span><strong>Order {{ $order->order_number ?? '#' . $order->id }}</strong></span>
                        <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Total:</strong> {{ number_format($order->total_amount) }} UGX</p>
                        <p><strong>Payment:</strong> {{ $order->payment_method }}
                            <span class="badge {{ $order->payment_badge }} ms-1">{{ $order->payment_label }}</span>
                        </p>
                        @if ($order->needsPayment())
                            <a href="{{ $order->paymentUrl() }}" class="btn btn-sm btn-success text-white me-1">
                                <i class="fas fa-mobile-alt me-1"></i> Pay Now
                            </a>
                        @endif
                        <a href="{{ route('customer.order.view', $order->id) }}"
                            class="btn btn-sm btn-primary text-white">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center mt-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5 class="card-title text-primary">No Orders Found</h5>
                            <p class="card-text text-muted">You haven't placed any orders yet. Start shopping to see
                                your orders here!</p>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary mt-2">
                                <i class="fas fa-shopping-cart me-1"></i> Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            @endempty

            </tbody>
    </div>
</div>
</div>

<script>
    const customerName = "{{ Helper::customer_name(Session('LoggedCustomer')) }}";

    if (sessionStorage.getItem('showWelcome') === 'true') {
        document.getElementById('dashboard-heading').innerText = `Hi ${customerName}! Welcome to your dashboard.`;
        sessionStorage.setItem('dashboardVisited', 'true');
        sessionStorage.removeItem('showWelcome');
    } else {
        document.getElementById('dashboard-heading').innerText = 'Dashboard Information';
    }
</script>

@include('layouts.footer')

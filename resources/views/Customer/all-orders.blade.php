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

<div class="container-fluid py-5">
    <div class="container bg-light">
        <h2 class="mb-4 text-primary text-center pt-4">Order #{{ $order->id }} Details</h2>
        <hr class="mb-4">

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-3">Order Summary</h5>
                        <p class="card-text mb-2"><strong>Status:</strong> <span
                                class="badge bg-info text-dark">{{ ucfirst($order->status) }}</span></p>
                        <p class="card-text mb-2"><strong>Total:</strong> <span
                                class="text-success fw-bold">{{ number_format($order->total_amount) }} UGX</span></p>
                        <p class="card-text"><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Shipping Information</h5>
                        {{-- shipping_info is cast to an array on the Order model,
                             so it arrives already decoded — json_decode() here
                             was being handed an array instead of a JSON string.

                             Checkout (OrderController::placeOrder) only ever
                             stores name, phone, email, address, city and
                             country — there is no "region" key, so it must
                             not be read directly; every key is guarded with
                             `?? 'N/A'` in case older orders are missing one. --}}
                        @php $shipping = (object) ($order->shipping_info ?? []); @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Name:</strong> {{ $shipping->name ?? 'N/A' }}</li>
                                    <li><strong>Phone:</strong> {{ $shipping->phone ?? 'N/A' }}</li>
                                    <li><strong>Email:</strong> {{ $shipping->email ?? 'N/A' }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Address:</strong> {{ $shipping->address ?? 'N/A' }}, {{ $shipping->city ?? 'N/A' }}</li>
                                    <li><strong>Country:</strong> {{ $shipping->country ?? 'N/A' }}</li>
                                    <li><strong>Note:</strong> {{ $shipping->note ?? 'N/A' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mt-5 mb-3 text-primary">Ordered Items</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" class="text-center">#</th>
                        <th scope="col">Product</th>
                        <th scope="col" class="text-center">Quantity</th>
                        <th scope="col" class="text-end">Unit Price</th>
                        <th scope="col" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $key => $item)
                        <tr>

                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->price) }} UGX</td>
                            <td class="text-end">{{ number_format($item->price * $item->quantity) }} UGX</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
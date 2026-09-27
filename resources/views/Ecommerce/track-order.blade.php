@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Track Your Order</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active text-white">Track Order</li>
    </ol>
</div>
<!-- Single Page Header End -->

<div class="container-fluid">
    <div class="container py-5">

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <p class="text-muted mb-4">
                    No account needed — enter the order number you were given at checkout together with the
                    email address you used to place the order.
                </p>

                @if (session('error') || ($errors->any()))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('order.track.submit') }}" method="POST" class="mb-5">
                    @csrf
                    <div class="form-item mb-3">
                        <label class="form-label">Order Number<sup>*</sup></label>
                        <input type="text" name="order_number" class="form-control"
                            value="{{ old('order_number', request('order_number')) }}" placeholder="e.g. SHN-4F2A9C1B"
                            required>
                    </div>
                    <div class="form-item mb-4">
                        <label class="form-label">Email Address<sup>*</sup></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', request('email')) }}" placeholder="you@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Track Order</button>
                </form>
            </div>
        </div>

        @if ($order)
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <div class="border rounded p-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Order {{ $order->order_number }}</h4>
                            <span class="badge bg-primary text-uppercase">{{ $order->status }}</span>
                        </div>

                        <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                        <p class="mb-3"><strong>Placed:</strong> {{ $order->created_at->format('d M Y, H:i') }}</p>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product->product_name ?? 'Product #' . $item->product_id }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price, 0) }} UGX</td>
                                            <td>{{ number_format($item->price * $item->quantity, 0) }} UGX</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                                        <td><strong>{{ number_format($order->total_amount, 0) }} UGX</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

@include('layouts.footer')

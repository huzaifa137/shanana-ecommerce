@extends('layouts2.master')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!---jvectormap css-->
    <link href="{{ URL::asset('assets2/plugins/jvectormap/jqvmap.css') }}" rel="stylesheet" />
    <!-- Data table css -->
    <link href="{{ URL::asset('assets2/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <!--Daterangepicker css-->
    <link href="{{ URL::asset('assets2/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
@endsection
@section('page-header')
    <!--Page header-->
    <div class="page-header">
        <div class="page-rightheader ml-auto d-lg-flex d-none">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="d-flex"><svg class="svg-icon"
                            xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                            <path d="M0 0h24v24H0V0z" fill="none" />
                            <path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3zm5 15h-2v-6H9v6H7v-7.81l5-4.5 5 4.5V18z" />
                            <path d="M7 10.19V18h2v-6h6v6h2v-7.81l-5-4.5z" opacity=".3" />
                        </svg><span class="breadcrumb-icon"> Home</span></a></li>
                <li class="breadcrumb-item"><a href="javascript:void();">Orders</a></li>
            </ol>
        </div>
    </div>
    <!--End Page header-->
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-12">
            <div class="container py-5">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div
                        class="card-header bg-primary text-white py-4 px-4 d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 text-white">Order {{ $order->order_number ?? '#' . $order->id }}</h2>
                        <form id="update-status-form" action="{{ route('admin.orders.updateStatus', $order->id) }}"
                            method="POST">
                            @csrf
                            <div class="input-group input-group-sm">
                                <select name="status" id="orderStatus"
                                    class="form-select form-control custom-select-width">
                                    @foreach (['pending', 'processing', 'shipped', 'delivered', 'canceled'] as $status)
                                        <option value="{{ $status }}"
                                            {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ $status === 'canceled' ? 'Cancelled' : ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Order Number:</strong> <span
                                        class="fw-bold">{{ $order->order_number ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong>Customer:</strong> {{ $order->customer_name ?: 'Guest' }}</p>
                                <p class="mb-2"><strong>Total Amount:</strong> <span
                                        class="text-success fw-bold">{{ number_format($order->total_amount) }}
                                        UGX</span></p>
                                <p class="mb-2"><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
                                <p class="mb-0"><strong>Payment Status:</strong>
                                    <span class="badge {{ $order->payment_badge }}">{{ $order->payment_label }}</span>
                                    @if ($order->paid_at)
                                        <span class="text-muted small ms-1">{{ $order->paid_at->format('M d, Y H:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="text-muted mb-0">Order placed on:
                                    {{ $order->created_at->format('M d, Y H:i A') }}
                                </p>
                            </div>
                        </div>

                        <h4 class="mt-4 mb-3 text-primary">Payments</h4>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Mobile Money No.</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($order->payments()->latest('id')->get() as $payment)
                                        <tr>
                                            <td class="text-nowrap">{{ ($payment->paid_at ?? $payment->created_at)->format('M d, Y H:i') }}</td>
                                            <td>{{ $payment->phone }}</td>
                                            <td>{{ number_format($payment->amount) }} UGX</td>
                                            <td>
                                                <span class="badge {{ $payment->status === 'successful' ? 'bg-success' : ($payment->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="small text-break">{{ $payment->reference }}</td>
                                            <td class="small">
                                                {{ $payment->failure_reason ?? '—' }}
                                                @if ($payment->status === 'failed' && data_get($payment->provider_response, 'message'))
                                                    <div class="text-muted">Provider: {{ data_get($payment->provider_response, 'message') }}
                                                        @if (data_get($payment->provider_response, 'error_code'))
                                                            ({{ data_get($payment->provider_response, 'error_code') }})
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No payment attempts yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <h4 class="mt-4 mb-3 text-primary">Shipping Information</h4>

                        {{-- shipping_info is cast to an array on the Order model,
                             so it arrives already decoded — json_decode() here
                             was being handed an array instead of a JSON string.

                             Checkout (OrderController::placeOrder) only ever
                             stores name, phone, email, address, city and
                             country — there is no "region" or "postcode" key,
                             so those must not be read directly; every key is
                             guarded with `?? 'N/A'` in case older orders are
                             missing one. --}}
                        @php $shipping = (object) ($order->shipping_info ?? []); @endphp

                        <div class="card bg-light p-3 mb-4 rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-2">
                                        <li><strong>Name:</strong> {{ $shipping->name ?? 'N/A' }}</li>
                                        <li><strong>Phone:</strong> {{ $shipping->phone ?? 'N/A' }}</li>
                                        <li><strong>Email:</strong> {{ $shipping->email ?? 'N/A' }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-2">
                                        <li><strong>Address:</strong> {{ $shipping->address ?? 'N/A' }}, {{ $shipping->city ?? 'N/A' }}
                                        </li>
                                        <li><strong>Country:</strong> {{ $shipping->country ?? 'N/A' }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-4 mb-3 text-primary">Ordered Items</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover bg-white shadow-sm rounded">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th scope="col">Product</th>
                                        <th scope="col" class="text-center">Quantity</th>
                                        <th scope="col" class="text-end">Unit Price</th>
                                        <th scope="col" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $key => $item)
                                        <tr>
                                            <td style="width: 1px">{{ $key + 1 }}.</td>
                                            <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->price) }} UGX</td>
                                            <td class="text-end">{{ number_format($item->price * $item->quantity) }} UGX
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end py-3 px-4">
                        <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('orderStatus');
            const form = document.getElementById('update-status-form');

            statusSelect.addEventListener('change', function() {
                const newStatus = this.value;
                const currentStatus = "{{ $order->status }}";

                if (newStatus === currentStatus) {
                    return; // No change, do nothing
                }

                Swal.fire({
                    title: 'Confirm Status Change?',
                    text: `Are you sure you want to change the order status to "${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Updating order status, please wait.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit the form
                        form.submit();
                    } else {
                        // If cancelled, revert the select box to its original value
                        statusSelect.value = currentStatus;
                    }
                });
            });

            // Listen for server-side success/error messages (if you're redirecting back)
            // This part assumes your Laravel controller redirects back with a session flash message
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 2000
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "{{ session('error') }}",
                    showConfirmButton: true
                });
            @endif
        });
    </script>

    <style>
        /* Adjust width of the select for smaller screens */
        .custom-select-width {
            width: auto !important;
            /* Adjust as needed */
            min-width: 120px;
            /* Minimum width */
        }

        @media (max-width: 767.98px) {
            .custom-select-width {
                min-width: unset;
                /* Remove min-width on small screens */
                width: 100% !important;
                /* Full width on small screens */
            }
        }
    </style>

    </div>
    </div><!-- end app-content-->
    </div>
@endsection
@section('js')
@endsection
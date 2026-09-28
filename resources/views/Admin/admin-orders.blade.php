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
    <!-- Row -->
    <div class="row">
        <div class="col-md-12 col-lg-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Orders</h3>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert"
                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert"
                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Order No.</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $key => $order)
                                    <tr>
                                        <td style="width: 1px;">{{ $orders->firstItem() + $loop->index }}</td>
                                        <td class="fw-bold text-nowrap">{{ $order->order_number ?? 'N/A' }}</td>
                                        <td>{{ $order->customer_name ?: 'Guest' }}</td>
                                        <td>{{ number_format($order->total_amount) }} UGX</td>
                                        <td>
                                            <input type="text" class="form-control" value="{{ ucfirst($order->status) }}"
                                                disabled>
                                        </td>
                                        <td>
                                            <div>{{ $order->payment_method }}</div>
                                            <span class="badge {{ $order->payment_badge }}">{{ $order->payment_label }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                                class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye me-1"></i> Details
                                            </a>
                                            <form action="{{ route('admin.orders.destroy', $order->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Delete order {{ $order->order_number ?? '#' . $order->id }}? It will be removed from the admin, customer and tracking views.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="alert alert-warning text-center mb-0">
                                                <i class="fas fa-exclamation-circle me-2"></i> No orders found.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                  
                     <div class="d-flex justify-content-end align-items-center p-3">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>

        </div>
    </div>
    </div>
    <!-- End Row -->

    </div>
    </div><!-- end app-content-->
    </div>
@endsection
@section('js')
@endsection

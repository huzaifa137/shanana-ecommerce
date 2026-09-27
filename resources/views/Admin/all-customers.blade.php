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
    <div class="row">
        <div class="col-md-12 col-lg-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Customers</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Country</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $index => $customer)
                                    <tr>
                                        <td>{{ $index + $customers->firstItem() }}</td>
                                        <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                                        <td>{{ $customer->mobile }}</td>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                        <td>{{ $customer->country ?? 'N/A' }}</td>
                                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.customers.show', $customer->id) }}"
                                                class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-warning text-center mb-0">
                                                <i class="fas fa-exclamation-circle me-2"></i> No customers found.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center p-3">
                        {{ $customers->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>

        </div>
    </div>
    </div>
    </div><!-- end app-content-->
    </div>
@endsection
@section('js')
@endsection

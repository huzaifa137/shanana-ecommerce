@include('layouts.header')


<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Cart</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Pages</a></li>
        <li class="breadcrumb-item active text-white">Cart</li>
    </ol>
</div>
<!-- Single Page Header End -->

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

<!-- Cart Page Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        @if (count($addedProducts) == 0)
            <tr>
                <td colspan="6">
                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                        <div class="text-center">
                            <i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h3 class="text-danger">Your cart is empty</h3>
                            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
                            <a href="{{ route('item.shop') }}" class="btn btn-primary mt-3">
                                <i class="fa fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Products</th>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total</th>
                            <th scope="col">Handle</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php
                            $subtotal = 0;
                        @endphp

                        @foreach ($addedProducts as $products)
                            <?php
                            $product = DB::table('products')->where('id', $products['id'])->first();
                            ?>

                            @php
                                $price = (int) str_replace(',', '', $product->sale_price);
                                $quantity = $products['quantity'] ?? 1;
                                $itemTotal = $price * $quantity;
                                $subtotal += $itemTotal;
                            @endphp

                            <tr>
                                <th scope="row">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('storage/' . $product->featured_image_1) }}"
                                            class="img-fluid me-5" style="width: 100px; height: 100px;" alt="">
                                    </div>
                                </th>
                                <td>
                                    <p class="mb-0 mt-4">{{ $product->product_name }}</p>
                                </td>
                                <td>
                                    <p class="mb-0 mt-4">{{ $product->sale_price }}</p>
                                </td>
                                <td>
                                    <div class="input-group quantity mt-4" style="width: 100px;">
                                        <div class="input-group-btn">
                                            <button
                                                class="btn btn-sm btn-minus rounded-circle bg-light border change-qty"
                                                data-change="-1" data-product-id="{{ $product->id }}">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                        <input type="text"
                                            class="form-control form-control-sm text-center border-0 quantity-input"
                                            data-product-id="{{ $product->id }}" value="{{ $quantity }}">

                                        <div class="input-group-btn">
                                            <button
                                                class="btn btn-sm btn-plus rounded-circle bg-light border change-qty"
                                                data-change="1" data-product-id="{{ $product->id }}">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                <td class="item-total" data-product-id="{{ $product->id }}">
                                    <p class="mb-0 mt-4">Ugx {{ number_format($itemTotal) }}/=</p>
                                </td>

                                <td>
                                    <a href="{{ route('shop.cart.remove', $products['id']) }}"
                                        class="btn btn-md btn-handle rounded-circle bg-light border mt-4 remove-from-cart"
                                        data-url="{{ route('shop.cart.remove', $products['id']) }}">
                                        <i class="fa fa-times text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>


                <div class="mt-5 d-flex justify-content-end">
                    <div class="p-5 border rounded shadow bg-light" style="max-width: 450px; width: 100%;">
                        <h4 class="fw-bold text-primary mb-4">Cart Summary</h4>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-secondary fs-5">Subtotal:</span>
                            <span class="subtotal-amount fw-bold text-dark fs-5">Ugx
                                {{ number_format($subtotal) }}/=</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            {{-- <span class="fw-semibold text-secondary fs-5">Shipping:</span> --}}
                            {{-- <span class="fw-normal text-dark fs-5">Ugx 3,000/=</span> --}}
                            <span class="shipping-amount fw-normal text-dark fs-5">Calculating Shiping Rates...</span>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold text-primary fs-4">Total:</span>
                            <span class="total-amount fw-bold text-dark fs-4">Ugx
                                {{ number_format($subtotal + 0) }}/=</span>
                        </div>

                        <div class="d-grid">
                            <a href="{{ url('/item-checkout') }}"
                                class="btn btn-primary rounded-pill py-3 fs-6 text-uppercase text-white fw-bold">
                                Proceed to Checkout</a>
                        </div>
                    </div>
                </div>


            </div>
        @endif
    </div>
</div>
<!-- Cart Page End -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.remove-from-cart');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const url = this.getAttribute('data-url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Remove this product from the cart?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Swal.fire({
                            title: 'Removing...',
                            html: '<i class="fas fa-spinner fa-spin fa-2x text-danger"></i>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                setTimeout(() => {
                                    window.location.href = url;
                                }, 300);
                            }
                        });
                    }
                });
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('.change-qty').off('click').on('click', function() {
            const productId = $(this).data('product-id');
            const change = parseInt($(this).data('change'));
            const input = $(`.quantity-input[data-product-id="${productId}"]`);
            let quantity = parseInt(input.val()) || 1;

            quantity += change;
            if (quantity < 1) quantity = 1;

            input.val(quantity);

            updateItemTotal(productId, quantity);
        });

        function updateItemTotal(productId, quantity) {
            $.ajax({
                url: "{{ route('shop.cart.updateQuantity') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        // Update per-item total
                        $(`td.item-total[data-product-id="${productId}"]`).html(
                            `<p class="mb-0 mt-4">Ugx ${formatNumber(response.item_total)}/=</p>`
                        );
                        // Update subtotal
                        $('.subtotal-amount').html(`Ugx ${formatNumber(response.subtotal)}/=`);

                        // Update total (subtotal + fixed shipping fee of 3000)
                        const total = response.subtotal + 0;
                        $('.total-amount').html(`Ugx ${formatNumber(total)}/=`);
                    }
                }
            });
        }

        // Helper to format numbers with commas
        function formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    });
</script>

<script>
    $(document).ready(function() {
        $.ajax({
            url: "{{ route('calculate.shipping') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                firstName: "Blake",
                lastName: "Ryan",
                address: "Kamuli,kireka road",
                city: "Kampala",
                country: "UG",
                postcode: "58792",
                mobile: "+1 (883) 115-6189",
                email: "kofa@mailinator.com"
            },
            success: function(response) {
                if (response.success) {
                    let shipping = parseFloat(response.rate);
                    $('.shipping-amount').text(`${response.currency} ${shipping}`);

                    const subtotal = {{ $subtotal ?? 0 }};

                    const total = subtotal + shipping;
                    $('.total-amount').html(`${response.currency} ${total.toLocaleString()}`);
                } else {
                    $('.shipping-amount').text('Shipping calculated at checkout');
                    console.error(response.message);
                }
            },
            // Shipping is a nice-to-have estimate on this page — never let a
            // failed/unconfigured shipping-rate lookup break the cart itself.
            error: function(jqXHR) {
                $('.shipping-amount').text('Shipping calculated at checkout');
                console.error('Shipping rate lookup failed:', jqXHR.responseJSON?.message || jqXHR.statusText);
            }
        });
    });
</script>


@include('layouts.footer')

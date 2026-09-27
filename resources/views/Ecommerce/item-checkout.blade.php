@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Checkout</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Pages</a></li>
        <li class="breadcrumb-item active text-white">Checkout</li>
    </ol>
</div>
<!-- Single Page Header End -->


<!-- Checkout Page Start -->
<div class="container-fluid ">
    <div class="container py-5">
        <h1 class="mb-4">Billing details</h1>
        @if (! $user)
            <div class="alert alert-info">
                Checking out as a guest — no account needed. Just fill in your details below and
                we'll use them to process (and let you track) your order. Already have an account?
                <a href="{{ route('user.login') }}">Log in</a> to check out faster next time.
            </div>
        @endif

        <form action="{{ route('order.place') }}" method="POST">
            @csrf
            <div class="row g-5">

                <div class="col-md-12 col-lg-12 col-xl-12">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th style="width: 30%">Product Name</th>
                                    <th style="width: 15%">Price</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 20%">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Total</span>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 small text-primary">Currency:</span>
                                                <select id="currency" class="form-select form-select-sm w-auto">
                                                    <!-- Currency options -->
                                                </select>
                                            </div>
                                        </div>
                                    </th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @forelse ($cart as $item)
                                    @php
                                        $product = DB::table('products')->where('id', $item['id'])->first();
                                        $total = $item['price'] * $item['quantity'];
                                        $subtotal += $total;
                                    @endphp
                                    <tr>
                                        <td>
                                            <img src="{{ asset('storage/' . $product->featured_image_1) }}"
                                                class="img-fluid" style="max-width: 70px; height: auto;" alt="">
                                        </td>
                                        <td class="text-start">{{ Str::limit($item['name'], 50) }}</td>
                                        <td data-ugx="{{ $item['price'] }}">{{ number_format($item['price'], 0) }} UGX
                                        </td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td data-ugx="{{ $total }}">{{ number_format($total, 0) }} UGX</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-danger">Your cart is empty.</td>
                                    </tr>
                                @endforelse

                                <tr>
                                    <td colspan="3"></td>
                                    <td><strong>Subtotal</strong></td>
                                    <td data-ugx="{{ $subtotal }}"><strong>{{ number_format($subtotal, 0) }}
                                            UGX</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 col-lg-12 col-xl-12">
                    <h1>Shipping Information</h1>

                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">First Name<sup>*</sup></label>
                                <input type="text" class="form-control" value="{{ $user->first_name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">Last Name<sup>*</sup></label>
                                <input type="text" class="form-control" value="{{ $user->last_name }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Company Name<sup>*</sup></label>
                        <input type="text" class="form-control" value="{{ $user->company_name }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Address <sup>*</sup></label>
                        <input type="text" class="form-control" value="{{ $user->address }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Town/City<sup>*</sup></label>
                        <input type="text" class="form-control" value="{{ $user->city }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Country<sup>*</sup></label>
                        <input type="text" class="form-control" value="{{ $user->country }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Postcode/Zip<sup>*</sup></label>
                        <input type="text" class="form-control" value="{{ $user->postcode }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Mobile<sup>*</sup></label>
                        <input type="tel" class="form-control" value="{{ $user->mobile }}" readonly>
                    </div>
                    <div class="form-item">
                        <label class="form-label my-3">Email Address<sup>*</sup></label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>

                    <hr>
                    <div class="form-check my-3">
                        <input class="form-check-input" type="checkbox" id="Address-1" name="Address"
                            value="Address" {{ $user->default_shipping_address == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="Address-1">Ship to a different address?</label>
                    </div>

                    <div class="form-item">
                        <textarea name="order_notes" class="form-control" spellcheck="false" cols="30" rows="5"
                            placeholder="Order Notes (Optional)"></textarea>
                    </div>
                </div>

                <div class="col-md-12 col-lg-12 col-xl-12">
                    <h2 class="mb-4">Available Payment Methods</h2>
                    <div class="row g-4">

                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between h-100">
                                <span>PayPal</span>
                                <img src="https://cdn-icons-png.flaticon.com/512/196/196565.png" alt="PayPal"
                                    width="40">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between h-100">
                                <span>Google Pay</span>
                                <img src="https://cdn-icons-png.flaticon.com/512/300/300221.png" alt="Google Pay"
                                    width="40">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between h-100">
                                <span>Stripe</span>
                                <img src="https://cdn-icons-png.flaticon.com/512/349/349221.png" alt="Stripe"
                                    width="40">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between h-100">
                                <span>Mobile Money</span>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="assets/img/airtel.jpg" alt="Airtel Money" width="30"
                                        height="30" style="object-fit: cover;">
                                    <img src="assets/img/mtn.jpg" alt="MTN Mobile Money" width="30"
                                        height="30" style="object-fit: cover;">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Make sure to include Font Awesome CDN in your <head> -->
                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

                    <div class="text-center mt-5">
                        <button type="submit" id="placeOrderBtn"
                            class="btn btn-primary btn-lg d-flex text-white align-items-center justify-content-center gap-2">
                            <i class="fas fa-shopping-cart"></i> Place Order
                        </button>
                    </div>

                </div>
            </div>
        </form>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#3085d6',
                    timer: 3500
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33',
                    timer: 4000
                });
            @endif
        </script>

    </div>
</div>
<!-- Checkout Page End -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const button = document.getElementById('placeOrderBtn');
        button.disabled = true;
        button.innerHTML = 'Placing Order...<i class="fas fa-spinner fa-spin"></i>';
    });
</script>


<script>
    const apiKey = 'd91f4ccfef7a4e238ee266b7';
    const baseCurrency = 'UGX';
    const currencySelect = document.getElementById('currency');
    const priceCells = document.querySelectorAll('td[data-ugx]');

    let rates = {};

    async function fetchRates() {
        try {
            const response = await fetch(`https://v6.exchangerate-api.com/v6/${apiKey}/latest/${baseCurrency}`);
            const data = await response.json();
            if (data.result !== 'success') {
                throw new Error('Failed to fetch exchange rates');
            }
            rates = data.conversion_rates;
            populateCurrencyDropdown(rates);
            convertCurrency(baseCurrency);
        } catch (error) {
            alert('Failed to fetch exchange rates. Please try again later.');
            console.error(error);
        }
    }

    function populateCurrencyDropdown(rates) {
        currencySelect.innerHTML = '';

        const currencyCodes = Object.keys(rates).sort();

        currencyCodes.forEach(code => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = code;
            currencySelect.appendChild(option);
        });

        currencySelect.value = baseCurrency;
    }

    function convertCurrency(toCurrency) {
        if (!rates[toCurrency]) {
            alert(`Conversion rate for ${toCurrency} not available.`);
            return;
        }

        const rate = rates[toCurrency];

        priceCells.forEach(cell => {
            const ugxValue = parseFloat(cell.dataset.ugx);
            if (!isNaN(ugxValue)) {
                const converted = ugxValue * rate;
                cell.textContent = converted.toLocaleString(undefined, {
                    maximumFractionDigits: 2
                }) + ' ' + toCurrency;
            }
        });
    }

    currencySelect.addEventListener('change', () => {
        convertCurrency(currencySelect.value);
    });

    fetchRates();
</script>

@include('layouts.footer')
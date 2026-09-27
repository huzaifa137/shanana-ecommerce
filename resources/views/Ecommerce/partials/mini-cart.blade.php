{{--
    Mini-cart content for the homepage's #offcanvasCart panel.

    Extracted out of Ecommerce/home.blade.php so it can be re-rendered on
    demand: previously this markup was only ever rendered once, on the
    initial page load, from whatever was in session('cart') at that moment.
    The "Add to cart" AJAX flow (public/assets/js/cart-ajax.js) only updated
    the small count badge (.js-cart-count / .js-cart-badge) afterwards — it
    never touched this HTML — so opening the cart panel kept showing "Your
    cart is empty" (or a stale item list) until a full page reload re-ran
    this template. ProductsController::addToCart() now renders this same
    partial server-side after every add and returns it as `cart_html`, and
    cart-ajax.js swaps it into #offcanvasCart .offcanvas-body, so the panel
    is correct without a reload.
--}}
<div class="order-md-last">
    <h4 class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-primary">Your cart</span>
        <span class="badge bg-primary rounded-pill">
            {{ count(session('cart', [])) }}
        </span>
    </h4>
    @php
        $cart = session('cart', []);
        $total = 0;
    @endphp
    @if (!empty($cart))
        <ul class="list-group mb-3">
            @foreach ($cart as $productId => $item)
                @php
                    $product = \App\Models\Product::find($productId);
                    $quantity = $item['quantity'] ?? 1;
                    $price = $product->sale_price * $quantity;
                    $total += $price;
                @endphp
                @if ($product)
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0">{{ $product->product_name }}</h6>
                            <small class="text-body-secondary">Qty: {{ $quantity }}</small>
                        </div>
                        <span class="text-body-secondary">Ugx {{ number_format($price) }}</span>
                    </li>
                @endif
            @endforeach
            <li class="list-group-item d-flex justify-content-between">
                <span>Total (UGX)</span>
                <strong>Ugx {{ number_format($total) }}</strong>
            </li>
        </ul>
        <a href="{{ route('item.cart') }}" class="w-100 btn btn-primary btn-lg">Continue to
            cart</a>
    @else
        <div class="text-center p-5">
            <i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Your cart is empty</h5>
            <p>Looks like you haven't added any products yet.</p>
            <a href="{{ route('item.shop') }}" class="btn btn-outline-primary">Start Shopping</a>
        </div>
    @endif
</div>

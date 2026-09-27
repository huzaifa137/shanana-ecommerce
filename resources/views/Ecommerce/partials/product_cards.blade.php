    <?php
    use App\Http\Controllers\Helper;
    ?>
    <style>
        /* =========================================================
        SHANANA — LUXURY PRODUCT CARDS
        ========================================================= */
        
        /* NEW: The wrapper for the card that handles the flex sizing */
        .product-card-wrapper {
            width: 100%;
        }
        
        @media (min-width: 576px) {
            .product-card-wrapper {
                width: calc(50% - 1rem); /* 2 items per row on small screens */
            }
        }
        
        @media (min-width: 992px) {
            .product-card-wrapper {
                width: calc(25% - 1rem); /* 4 items per row on desktop */
            }
        }

        .modern-product-card {
            position: relative;
            height: 100%;
            background: #fff;
            border: 1px solid rgba(217, 79, 123, .08);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 7px 28px rgba(70, 35, 48, .055);
            transition: transform .4s cubic-bezier(.2, .8, .2, 1), box-shadow .4s ease, border-color .3s ease;
        }

        .modern-product-card:hover {
            transform: translateY(-8px);
            border-color: rgba(217, 79, 123, .16);
            box-shadow: 0 22px 48px rgba(70, 35, 48, .12);
        }

        /* =========================================================
        IMAGE AREA
        ========================================================= */
        .modern-product-media {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background: linear-gradient(145deg, #fff8fa 0%, #fcecef 100%);
        }

        .modern-product-media::before {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            top: -90px;
            right: -65px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .72);
            z-index: 1;
            pointer-events: none;
        }

        .modern-product-media a.d-block {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .modern-product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 1.25rem;
            display: block;
            transition: transform .65s cubic-bezier(.2, .8, .2, 1);
        }

        .modern-product-card:hover .modern-product-media img {
            transform: scale(1.065);
        }

        /* =========================================================
        DISCOUNT BADGE
        ========================================================= */
        .modern-discount-flag {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 4;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: .38rem .65rem;
            border-radius: 50px;
            background: #d94f7b;
            color: #fff;
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .04em;
            box-shadow: 0 6px 16px rgba(217, 79, 123, .22);
        }

        /* =========================================================
        WISHLIST
        ========================================================= */
        .modern-wish-btn {
            position: absolute;
            top: 13px;
            right: 13px;
            z-index: 5;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #d94f7b;
            border: 1px solid rgba(255, 255, 255, .9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #FFF;
            box-shadow: 0 6px 18px rgba(50, 25, 35, .08);
            transition: transform .3s ease, background .3s ease, color .3s ease, box-shadow .3s ease;
        }

        .modern-wish-btn:hover {
            background: #d94f7b;
            color: #fff;
            transform: scale(1.08);
            box-shadow: 0 8px 20px rgba(217, 79, 123, .25);
        }

        /* =========================================================
        QUICK VIEW
        ========================================================= */
        .modern-quickview {
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 14px;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: .6rem 1rem;
            border-radius: 50px;
            background: #bd3d68;
            border: 1px solid rgba(255, 255, 255, .95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #FFF;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .3s ease, transform .35s ease, background .3s ease, color .3s ease;
        }

        .modern-product-card:hover .modern-quickview {
            opacity: 1;
            transform: translateY(0);
        }

        .modern-quickview:hover {
            background: #d94f7b;
            color: #fff;
        }

        /* =========================================================
        PRODUCT BODY
        ========================================================= */
        .modern-product-body {
            padding: 1.05rem 1.1rem 1.15rem;
            background: #fff;
        }

        /* =========================================================
        RATING
        ========================================================= */
        .modern-rating {
            display: flex;
            align-items: center;
            gap: 2px;
            margin-bottom: .42rem;
            color: #e2a84a;
            font-size: .7rem;
        }

        .modern-rating svg {
            width: 11px;
            height: 11px;
        }

        .modern-rating span {
            margin-left: 5px;
            color: #9b858d;
            font-size: .68rem;
        }

        /* =========================================================
        PRODUCT NAME
        ========================================================= */
        .modern-product-name {
            margin: 0 0 .45rem;
            min-height: 2.5rem;
            font-size: .91rem;
            font-weight: 700;
            line-height: 1.38;
        }

        .modern-product-name a {
            color: #30242a;
            text-decoration: none;
            transition: color .25s ease;
        }

        .modern-product-name a:hover {
            color: #d94f7b;
        }

        /* =========================================================
        PRICE
        ========================================================= */
        .modern-price-row {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: .9rem;
        }

        .modern-price-now {
            font-family: 'Playfair Display', Georgia, serif;
            color: #30242a;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .modern-price-old {
            color: #E30048;
            font-size: .72rem;
        }

        /* =========================================================
        CART AREA
        ========================================================= */
        .modern-cart-row {
            width: 100%;
        }

        .modern-cart-row form,
        .modern-cart-row>div {
            width: 100%;
        }

        /* Quantity */
        .modern-qty-input {
            width: 48px;
            height: 39px;
            flex: 0 0 48px;
            padding: 0 .25rem;
            text-align: center;
            border: 1px solid #eadde2;
            border-radius: 10px;
            background: #fcf8fa;
            color: #4a3940;
            font-size: .78rem;
            font-weight: 600;
            transition: border-color .25s ease, background .25s ease;
        }

        .modern-qty-input:focus {
            outline: none;
            border-color: #d94f7b;
            background: #fff;
        }

        .modern-qty-input::-webkit-inner-spin-button,
        .modern-qty-input::-webkit-outer-spin-button {
            opacity: .5;
        }

        /* =========================================================
        ADD TO CART
        ========================================================= */
        .modern-add-btn,
        .modern-added-btn {
            flex: 1;
            min-width: 0;
            height: 39px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border-radius: 10px;
            font-size: .7rem;
            font-weight: 750;
            letter-spacing: .025em;
            text-decoration: none;
            transition: transform .25s ease, background .25s ease, box-shadow .25s ease;
        }

        .modern-add-btn {
            border: 1px solid #d94f7b;
            background: #d94f7b;
            color: #fff;
            cursor: pointer;
        }

        .modern-add-btn:hover {
            background: #bd3d68;
            border-color: #bd3d68;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(189, 61, 104, .22);
        }

        .modern-add-btn svg {
            flex-shrink: 0;
        }

        /* =========================================================
        IN CART
        ========================================================= */
        .modern-added-btn {
            background: #f2f8f3;
            color: #347648;
            border: 1px solid #d5e8d8;
            cursor: default;
        }

        /* =========================================================
        RESPONSIVE
        ========================================================= */
        @media (max-width: 575px) {
            .modern-product-card {
                border-radius: 18px;
            }

            .modern-product-media img {
                padding: 1rem;
            }

            .modern-product-body {
                padding: .9rem;
            }

            .modern-product-name {
                font-size: .85rem;
            }

            .modern-price-now {
                font-size: .98rem;
            }

            .modern-quickview {
                display: none;
            }
        }
    </style>

    @foreach ($products as $product)
        @php
            $cart = session('cart', []);
            $isInCart = array_key_exists($product->id, $cart);
            $cartQty = $isInCart ? $cart[$product->id]['quantity'] ?? 1 : 1;
            $reviewCounts = DB::table('product_reviews')
                ->where('product_id', $product->id)
                ->count();
            $displayReviewCount = $reviewCounts == 0 ? rand(1, 4) : $reviewCounts;
            $discount = 0;
            if ($product->price > 0 && $product->sale_price < $product->price) {
                $discount = round(
                    (($product->price - $product->sale_price) / $product->price) * 100
                );
            }
        @endphp

        <!-- The wrapper now uses our custom .product-card-wrapper class for flexbox sizing -->
        <div class="product-card-wrapper">
            <div class="modern-product-card h-100 d-flex flex-column position-relative">
                {{-- =====================================================
                PRODUCT IMAGE
                ===================================================== --}}
                <div class="modern-product-media">
                    <a href="{{ url('/product-item/' . $product->id) }}" title="{{ $product->product_name }}"
                        class="d-block h-100">
                        <img src="{{ asset('storage/' . $product->featured_image_1) }}" alt="{{ $product->product_name }}"
                            loading="lazy">
                    </a>
                    {{-- Discount --}}
                    @if ($discount > 0)
                        <span class="modern-discount-flag">
                            -{{ $discount }}%
                        </span>
                    @endif
                    {{-- Wishlist --}}
                    <a href="javascript:void(0);" class="modern-wish-btn" title="Save for later">
                        <svg width="16" height="16">
                            <use xlink:href="#heart"></use>
                        </svg>
                    </a>
                    {{-- Quick View --}}
                    <a href="{{ url('/product-item/' . $product->id) }}" class="modern-quickview">
                        View Product
                        <svg width="13" height="13" style="margin-left:5px;">
                            <use xlink:href="#arrow-right"></use>
                        </svg>
                    </a>
                </div>
                {{-- =====================================================
                PRODUCT INFORMATION
                ===================================================== --}}
                <div class="modern-product-body flex-grow-1 d-flex flex-column">
                    {{-- Rating --}}
                    <div class="modern-rating">
                        @for ($i = 0; $i < 5; $i++)
                            <svg width="12" height="12">
                                <use xlink:href="#star-full"></use>
                            </svg>
                        @endfor
                        <span>
                            ({{ $displayReviewCount }})
                        </span>
                    </div>
                    {{-- Product Name --}}
                    <h3 class="modern-product-name">
                        <a href="{{ url('/product-item/' . $product->id) }}">
                            {{ Str::limit($product->product_name, 30) }}
                        </a>
                    </h3>
                    {{-- Price --}}
                    <div class="modern-price-row">
                        <span class="modern-price-now">
                            Ugx{{ Helper::abbreviate_number($product->sale_price) }}
                        </span>
                        @if ($discount > 0)
                            <del class="modern-price-old">
                                Ugx{{ Helper::abbreviate_number($product->price) }}
                            </del>
                        @endif
                    </div>
                    {{-- =================================================
                    CART
                    ================================================= --}}
                    <div class="mt-auto modern-cart-row">
                        @if ($isInCart)
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" class="modern-qty-input" value="{{ $cartQty }}" min="1" max="9" readonly>
                                <a href="javascript:void(0);" class="modern-added-btn" tabindex="-1">
                                    <svg width="14" height="14">
                                        <use xlink:href="#check"></use>
                                    </svg>
                                    In Cart
                                </a>
                            </div>
                        @else
                            <form method="POST" action="{{ route('shop.add.cart', $product->id) }}"
                                class="d-flex align-items-center gap-2">
                                @csrf
                                <input type="number" name="quantity" class="modern-qty-input quantity" value="1" min="1" max="9">
                                <button type="submit" class="modern-add-btn">
                                    <svg width="14" height="14">
                                        <use xlink:href="#cart"></use>
                                    </svg>
                                    Add to Cart
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
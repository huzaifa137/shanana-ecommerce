@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Shop</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Pages</a></li>
        <li class="breadcrumb-item active text-white">Shop</li>
    </ol>
</div>
<!-- Single Page Header End -->

<?php
use App\Http\Controllers\Helper;
?>

<style>
    .vesitable-item {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .vesitable-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
    }

    .pagination .page-item {
        display: inline-block;
        margin: 0 4px;
    }

    .pagination .page-link {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        color: #007bff;
        text-decoration: none;
    }

    .pagination .active .page-link {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
</style>

<!-- Fruits Shop Start-->
<div class="container-fluid fruite py-5">
    <div class="container py-5">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h1 class="mb-4">Shanana Products</h1>
        <div class="row g-4">
            <div class="col-lg-12">

                <div class="row g-4">
                    <div class="col-xl-3">
                        <div class="input-group w-100 mx-auto d-flex">
                            <input type="search" class="form-control p-3" placeholder="keywords"
                                aria-describedby="search-icon-1">
                            <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                    <div class="col-6"></div>
                    <div class="col-xl-3">
                        <div class="bg-light ps-3 py-3 rounded d-flex justify-content-between mb-4">
                            <label for="fruits">Default Sorting: None</label>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-3">
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <h4>Categories</h4>
                                    <ul class="list-unstyled fruite-categorie">
                                        @foreach ($categories as $category)
                                                                                <?php
                                            $countCategory = DB::table('products')->where('category', $category->id)->count();
                                                                                    ?>
                                                                                <li>
                                                                                    <div class="d-flex justify-content-between fruite-name">
                                                                                        <a href="{{ url('/item-categories/' . $category->id) }}"><i
                                                                                                class="fas fa-apple-alt me-2"></i>{{ $category->name }}</a>
                                                                                        <span>({{ $countCategory }})</span>
                                                                                    </div>
                                                                                </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <h4 class="mb-4">Featured products</h4>

                                @foreach ($featuredProducts as $featuredProduct)
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="me-3 flex-shrink-0">
                                            <img src="{{ asset('storage/' . $featuredProduct->featured_image_2) }}"
                                                class="img-fluid rounded border" alt="{{ $featuredProduct->product_name }}"
                                                style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold text-dark">
                                                {{ $featuredProduct->product_name }}
                                            </h6>

                                            <div class="d-flex mb-2">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i class="fa fa-star text-warning me-1" style="font-size: 14px;"></i>
                                                @endfor
                                            </div>

                                            <div class="d-flex flex-column">
                                                <span class="text-primary fw-semibold" style="font-size: 14px;">
                                                    UGX {{ number_format($featuredProduct->sale_price) }}/=
                                                </span>
                                                <span class="text-decoration-line-through" style="color:#E30048;"
                                                    style="font-size: 13px;">
                                                    UGX {{ number_format($featuredProduct->price) }}/=
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>


                    @php
                        $randomNames = [
                            'Alice',
                            'Bob',
                            'Charlie',
                            'Diana',
                            'Ethan',
                            'Fiona',
                            'George',
                            'Hannah',
                            'Isaac',
                            'Jasmine',
                            'Kevin',
                            'Laura',
                            'Michael',
                            'Nina',
                            'Oscar',
                            'Paula',
                            'Quentin',
                            'Rachel',
                            'Samuel',
                            'Tina',
                            'Umar',
                            'Violet',
                            'William',
                            'Xena',
                            'Yusuf',
                            'Zara',
                            'Aaron',
                            'Bella',
                            'Caleb',
                            'Delilah',
                            'Edward',
                            'Faith',
                            'Gavin',
                            'Hailey',
                            'Ian',
                            'Julia',
                            'Kyle',
                            'Lily',
                            'Marcus',
                            'Natalie',
                            'Owen',
                            'Penelope',
                            'Ray',
                            'Sophia',
                            'Tristan',
                            'Uma',
                            'Victor',
                            'Wendy',
                            'Xander',
                            'Yara',
                        ];

                        $countries = [
                            'Europe' => [
                                'Germany',
                                'France',
                                'Spain',
                                'Italy',
                                'Netherlands',
                                'Sweden',
                                'Norway',
                                'Poland',
                                'Portugal',
                                'Greece',
                            ],
                            'Africa' => [
                                'Nigeria',
                                'Kenya',
                                'South Africa',
                                'Egypt',
                                'Ghana',
                                'Morocco',
                                'Algeria',
                                'Ethiopia',
                                'Tunisia',
                                'Uganda',
                            ],
                            'UK' => ['England', 'Scotland', 'Wales', 'Northern Ireland'],
                            'America' => [
                                'USA',
                                'Canada',
                                'Brazil',
                                'Mexico',
                                'Argentina',
                                'Colombia',
                                'Chile',
                                'Peru',
                                'Venezuela',
                                'Ecuador',
                            ],
                        ];

                        $allCountries = array_merge(...array_values($countries));

                        $salesData = $popupProducts
                            ->map(function ($product) use ($randomNames, $allCountries) {
                                return [
                                    'name' => $randomNames[array_rand($randomNames)],
                                    'product' => $product->product_name,
                                    'image' => asset('storage/' . $product->featured_image_1),
                                    'delivery' => rand(1, 4) . ' day delivery',
                                    'country' => $allCountries[array_rand($allCountries)],
                                ];
                            })
                            ->toArray();
                    @endphp

                    <div id="sales-popup"
                        class="toast show align-items-center border shadow position-fixed bottom-0 start-0 m-3"
                        role="alert" aria-live="assertive" aria-atomic="true"
                        style="min-width: 300px; display:none; z-index: 1050; background-color: #F6DDD8; position: relative;">

                        <!-- Close Button -->
                        <button id="sales-popup-close" aria-label="Close popup"
                            style="position: absolute; top: 5px; right: 8px; background: transparent; border: none; font-weight: bold; font-size: 16px; cursor: pointer;">
                            &times;
                        </button>

                        <div class="d-flex">
                            <img id="sales-popup-image" src="" alt="Product Image" class="rounded m-2"
                                style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="toast-body ps-0">
                                <div><strong id="sales-popup-name"></strong> from <span id="sales-popup-country"></span>
                                </div>
                                <div>Just purchased</div>
                                <div class="fw-bold" id="sales-popup-product"></div>
                                <small class="text-muted" id="sales-popup-delivery"></small>
                            </div>
                        </div>
                    </div>

                    <script>
                        @if (session()->has('reset_popup'))
                            localStorage.removeItem('salesPopupClosedAt');
                            @php session()->forget('reset_popup'); @endphp
                        @endif

                        const salesData = @json($salesData);
                        let currentIndex = 0;
                        const popup = document.getElementById('sales-popup');
                        const img = document.getElementById('sales-popup-image');
                        const nameEl = document.getElementById('sales-popup-name');
                        const productEl = document.getElementById('sales-popup-product');
                        const deliveryEl = document.getElementById('sales-popup-delivery');
                        const countryEl = document.getElementById('sales-popup-country');
                        const closeBtn = document.getElementById('sales-popup-close');

                        function showPopup(index) {
                            const sale = salesData[index];
                            img.src = sale.image;
                            nameEl.textContent = sale.name;
                            productEl.textContent = sale.product;
                            deliveryEl.textContent = sale.delivery;
                            countryEl.textContent = sale.country;
                            popup.style.display = 'flex';
                        }

                        function cycleSales() {
                            showPopup(currentIndex);
                            currentIndex = (currentIndex + 1) % salesData.length;
                        }

                        let interval;
                        const closedAt = localStorage.getItem('salesPopupClosedAt');
                        const now = Date.now();
                        const twelveHours = 12 * 60 * 60 * 1000; // 12 hours in milliseconds

                        if (!closedAt || now - closedAt > twelveHours) {
                            cycleSales();
                            interval = setInterval(cycleSales, 5000);
                        } else {
                            popup.style.display = 'none';
                        }

                        closeBtn.addEventListener('click', () => {
                            popup.style.display = 'none';
                            localStorage.setItem('salesPopupClosedAt', Date.now());
                            if (interval) clearInterval(interval);
                        });
                    </script>

                    <div class="col-lg-9">
                        <div class="row g-4 justify-content-center">
                            @php
                                $cart = session('cart', []);
                            @endphp

                            @foreach ($products as $product)
                                @php
                                    $isInCart = array_key_exists($product->id, $cart);
                                @endphp

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="border border-primary rounded position-relative vesitable-item h-100">
                                        {{-- Make the entire card clickable --}}
                                        <a href="{{ url('/product-item/' . $product->id) }}" class="stretched-link"></a>

                                        <div class="vesitable-img">
                                            <img src="{{ asset('storage/' . $product->featured_image_1) }}"
                                                class="img-fluid w-100 rounded-top" alt="">
                                        </div>

                                        <div class="text-white bg-primary px-3 py-1 rounded position-absolute"
                                            style="top: 10px; left: 10px; font-weight: 600; z-index: 2;">
                                            {{ Helper::product_category_name($product->category) }}
                                        </div>

                                        <div class="p-4 pb-0 rounded-bottom position-relative" style="z-index: 2;">
                                            <h4 class="text-dark fw-bold">{{ $product->product_name }}</h4>

                                            @php
                                                $plainText = strip_tags($product->description);
                                                $wordLimit = 15;
                                                $shortDescription = Str::words($plainText, $wordLimit, '...');
                                            @endphp
                                            <p class="text-muted">{{ $shortDescription }}</p>

                                            <div class="d-flex justify-content-between flex-lg-wrap">
                                                <p class="text-primary fs-5 fw-bold mb-0">Ugx
                                                    {{ $product->sale_price }}/=
                                                </p>

                                                @if ($isInCart)
                                                    <button type="button"
                                                        class="btn border border-success rounded-pill px-3 py-1 mt-4 mb-4 text-success"
                                                        disabled style="z-index: 3; position: relative;">
                                                        <i class="fa fa-check me-2 text-success"></i> In Cart
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('shop.add.cart', $product->id) }}"
                                                        style="z-index: 3; position: relative;">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn border border-primary rounded-pill px-3 py-1 mt-4 mb-4 text-primary">
                                                            <i class="fa fa-shopping-bag me-2 text-primary"></i>
                                                            Add to cart
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <style>
                        .stretched-link {
                            position: absolute;
                            top: 0;
                            left: 0;
                            bottom: 0;
                            right: 0;
                            z-index: 1;
                            text-indent: -9999px;
                        }
                    </style>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fruits Shop End-->

@include('layouts.footer')